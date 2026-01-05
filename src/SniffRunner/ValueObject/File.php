<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\SniffRunner\ValueObject;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File as BaseFile;
use PHP_CodeSniffer\Fixer;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\Exception\ShouldNotHappenException;
use Symplify\EasyCodingStandard\FileSystem\StaticRelativeFilePathHelper;
use Symplify\EasyCodingStandard\Skipper\Skipper\Skipper;
use Symplify\EasyCodingStandard\SniffRunner\DataCollector\SniffMetadataCollector;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;

/**
 * @api
 * @see \Symplify\EasyCodingStandard\Tests\SniffRunner\ValueObject\FileTest
 */
final class File extends BaseFile
{
    private string $relativePath;

    private string|null $activeSniffClass = null;

    private string|null $previousActiveSniffClass = null;

    /**
     * @var array<int|string, Sniff[]>
     */
    private array $tokenListeners = [];

    private ?string $filePath = null;

    /**
     * @var array<class-string<Sniff>, int> A map of which sniffers have
     *     requested themselves to be disabled, pointing to the index in
     *     the files token stack that they are to be re-enabled.
     */
    private array $disabledSniffers = [];

    /**
     * @var array<class-string<Sniff>>
     */
    private array $reportSniffClassesWarnings = [];

    public function __construct(
        string $path,
        string $content,
        Fixer $fixer,
        private Skipper $skipper,
        private SniffMetadataCollector $sniffMetadataCollector,
        private EasyCodingStandardStyle $easyCodingStandardStyle
    ) {
        $this->path = $path;
        $this->relativePath = StaticRelativeFilePathHelper::resolveFromCwd($path);
        $this->content = $content;

        // this property cannot be promoted as defined in constructor
        $this->fixer = $fixer;

        $this->eolChar = Common::detectLineEndings($content);

        // compat
        if (! defined('PHP_CODESNIFFER_CBF')) {
            define('PHP_CODESNIFFER_CBF', false);
        }

        // parent required
        $this->config = new Config([], false);
        $this->config->tabWidth = 4;
        $this->config->annotations = false;
        $this->config->encoding = 'UTF-8';
    }

    /**
     * Mimics @see
     * https://github.com/squizlabs/PHP_CodeSniffer/blob/e4da24f399d71d1077f93114a72e305286020415/src/Files/File.php#L310
     */
    public function process(): void
    {
        // Since sniffs are re-run after they do fixes, we need to clear the old
        // errors to avoid duplicates.
        $this->sniffMetadataCollector->resetErrors();

        $this->parse();
        $this->fixer->startFile($this);

        $currentFilePath = $this->filePath;
        if (! is_string($currentFilePath)) {
            throw new ShouldNotHappenException();
        }

        foreach ($this->tokens as $stackPtr => $token) {
            if (! isset($this->tokenListeners[$token['code']])) {
                continue;
            }

            foreach ($this->tokenListeners[$token['code']] as $sniff) {
                $shouldSkipSniff = $this->skipper->shouldSkipElementAndFilePath($sniff, $currentFilePath);
                $sniffIsDisabled = $this->isSniffStillDisabled($sniff::class, $stackPtr);

                if ($shouldSkipSniff || $sniffIsDisabled) {
                    continue;
                }

                $this->reportActiveSniffClass($sniff);
                $this->disableSnifferUntil($sniff::class, $sniff->process($this, $stackPtr));
            }
        }

        $this->fixedCount += $this->fixer->getFixCount();
        $this->disabledSniffers = [];
    }

    /**
     * Delegate to addError().
     *
     * @param mixed[] $data
     */
    public function addFixableError(
        string $error,
        int $stackPtr,
        string $code,
        array $data = [],
        int $severity = 0
    ): bool {
        $fullyQualifiedCode = $this->resolveFullyQualifiedCode($code);
        $this->sniffMetadataCollector->addAppliedSniff($fullyQualifiedCode);

        return ! $this->shouldSkipError($error, $code, $data);
    }

    /**
     * @param mixed[] $data
     */
    public function addError(
        string $error,
        ?int $stackPtr,
        string $code,
        array $data = [],
        int $severity = 0,
        bool $fixable = false
    ): bool {
        if ($this->shouldSkipError($error, $code, $data)) {
            return false;
        }

        return parent::addError($error, $stackPtr, $code, $data, $severity, $fixable);
    }

    /**
     * @param mixed[] $data
     * Allow only specific classes
     */
    public function addWarning(
        string $warning,
        ?int $stackPtr,
        string $code,
        array $data = [],
        int $severity = 0,
        bool $fixable = false
    ): bool {
        if ($this->activeSniffClass === null) {
            throw new ShouldNotHappenException();
        }

        if ($this->shouldSkipClassWarnings($this->activeSniffClass)) {
            return false;
        }

        return $this->addError($warning, $stackPtr, $code, $data, $severity, $fixable);
    }

    /**
     * @param array<class-string<Sniff>> $reportSniffClassesWarnings
     * @param array<int|string, Sniff[]> $tokenListeners
     */
    public function processWithTokenListenersAndFilePath(
        array $tokenListeners,
        string $filePath,
        array $reportSniffClassesWarnings
    ): void {
        $this->tokenListeners = $tokenListeners;
        $this->filePath = $filePath;
        $this->reportSniffClassesWarnings = $reportSniffClassesWarnings;
        $this->process();
    }

    /**
     * @param mixed[] $data
     * Delegated from addError().
     */
    protected function addMessage(
        bool $isError,
        string $message,
        int $line,
        int $column,
        string $sniffClassOrCode,
        array $data,
        int $severity,
        bool $isFixable = false
    ): bool {
        // skip warnings
        if (! $isError) {
            return false;
        }

        // hardcode skip the PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff.FoundInWhileCondition
        // as the only code is passed and this rule does not make sense
        if ($sniffClassOrCode === 'FoundInWhileCondition') {
            return false;
        }

        $message = $data !== [] ? vsprintf($message, $data) : $message;

        $checkerClass = $this->resolveFullyQualifiedCode($sniffClassOrCode);
        $codingStandardError = new CodingStandardError($line, $message, $checkerClass, $this->relativePath);

        $this->sniffMetadataCollector->addCodingStandardError($codingStandardError);

        if ($isFixable) {
            return $isFixable;
        }

        // do not add non-fixable errors twice
        return $this->fixer->loops === 0;
    }

    private function reportActiveSniffClass(Sniff $sniff): void
    {
        // used in other places later
        $this->activeSniffClass = $sniff::class;

        if (! $this->easyCodingStandardStyle->isDebug()) {
            return;
        }

        if ($this->previousActiveSniffClass === $this->activeSniffClass) {
            return;
        }

        $this->easyCodingStandardStyle->writeln('     [sniff] ' . $this->activeSniffClass);
        $this->previousActiveSniffClass = $this->activeSniffClass;
    }

    private function resolveFullyQualifiedCode(string $sniffClassOrCode): string
    {
        if (class_exists($sniffClassOrCode)) {
            return $sniffClassOrCode;
        }

        return $this->activeSniffClass . '.' . $sniffClassOrCode;
    }

    /**
     * @param string[] $data
     */
    private function shouldSkipError(string $error, string $code, array $data): bool
    {
        $fullyQualifiedCode = $this->resolveFullyQualifiedCode($code);

        if (! is_string($this->filePath)) {
            throw new ShouldNotHappenException();
        }

        if ($this->skipper->shouldSkipElementAndFilePath($fullyQualifiedCode, $this->filePath)) {
            return true;
        }

        $message = $data !== [] ? vsprintf($error, $data) : $error;

        return $this->skipper->shouldSkipElementAndFilePath($message, $this->filePath);
    }

    private function shouldSkipClassWarnings(string $sniffClass): bool
    {
        foreach ($this->reportSniffClassesWarnings as $reportSniffClassWarning) {
            if (is_a($sniffClass, $reportSniffClassWarning, true)) {
                return false;
            }
        }

        return true;
    }

    private function isSniffStillDisabled(string $sniffClass, int $targetStackPtr): bool
    {
        $disabledUntil = $this->disabledSniffers[$sniffClass] ?? 0;

        if ($disabledUntil > $targetStackPtr) {
            return true;
        }

        unset($this->disabledSniffers[$sniffClass]);
        return false;
    }

    /**
     * @param class-string<Sniff> $sniffClass
     */
    private function disableSnifferUntil(string $sniffClass, ?int $targetStackPtr = null): void
    {
        if ($targetStackPtr === null) {
            return;
        }

        $this->disabledSniffers[$sniffClass] = $targetStackPtr;
    }
}
