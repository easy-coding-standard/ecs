<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Output;

use Symplify\EasyCodingStandard\Console\ExitCode;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;
use Symplify\EasyCodingStandard\ValueObject\Configuration;
use Symplify\EasyCodingStandard\ValueObject\Error\ErrorAndDiffResult;
use Symplify\EasyCodingStandard\ValueObject\Error\SystemError;
/**
 * @see \Symplify\EasyCodingStandard\Tests\Console\Output\JUnitOutputFormatterTest
 */
final class JUnitOutputFormatter implements OutputFormatterInterface
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle
     */
    private $easyCodingStandardStyle;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Console\Output\ExitCodeResolver
     */
    private $exitCodeResolver;
    public function __construct(EasyCodingStandardStyle $easyCodingStandardStyle, \Symplify\EasyCodingStandard\Console\Output\ExitCodeResolver $exitCodeResolver)
    {
        $this->easyCodingStandardStyle = $easyCodingStandardStyle;
        $this->exitCodeResolver = $exitCodeResolver;
    }
    /**
     * @return ExitCode::*
     */
    public function report(ErrorAndDiffResult $errorAndDiffResult, Configuration $configuration) : int
    {
        $xml = $this->createXmlOutput($errorAndDiffResult, $configuration->isReportingWithRealPath());
        $this->easyCodingStandardStyle->writeln($xml);
        return $this->exitCodeResolver->resolve($errorAndDiffResult, $configuration);
    }
    public static function getName() : string
    {
        return 'junit';
    }
    public static function hasSupportForProgressBars() : bool
    {
        return \false;
    }
    /**
     * @api
     */
    public function createXmlOutput(ErrorAndDiffResult $errorAndDiffResult, bool $absoluteFilePath = \false) : string
    {
        $result = '<?xml version="1.0" encoding="UTF-8"?>';
        $totalFailuresCount = $errorAndDiffResult->getErrorCount();
        $totalTestsCount = $errorAndDiffResult->getFileDiffsCount();
        $result .= \sprintf('<testsuite failures="%d" name="easy-coding-standard" tests="%d" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd">', $totalFailuresCount, $totalTestsCount);
        foreach ($errorAndDiffResult->getErrors() as $codingStandardError) {
            $fileName = $absoluteFilePath ? $codingStandardError->getAbsoluteFilePath() : $codingStandardError->getRelativeFilePath();
            $result .= $this->createTestCase(\sprintf('%s:%s', $fileName, $codingStandardError->getLine()), $codingStandardError->getMessage());
        }
        foreach ($errorAndDiffResult->getSystemErrors() as $systemError) {
            if ($systemError instanceof SystemError) {
                $result .= $this->createTestCase($systemError->getFileWithLine(), $systemError->getMessage());
            }
        }
        foreach ($errorAndDiffResult->getFileDiffs() as $codingStandardError) {
            $fileName = $absoluteFilePath ? $codingStandardError->getAbsoluteFilePath() : $codingStandardError->getRelativeFilePath();
            $result .= $this->createTestCase($fileName ?? '', $codingStandardError->getDiff());
        }
        return $result . '</testsuite>';
    }
    /**
     * Format a single test case
     */
    private function createTestCase(string $reference, ?string $message = null) : string
    {
        $result = \sprintf('<testcase name="%s">', $this->escape($reference));
        if ($message !== null) {
            $result .= \sprintf('<failure type="%s" message="%s" />', 'ERROR', $this->escape($message));
        }
        return $result . '</testcase>';
    }
    /**
     * Escapes values for using in XML
     */
    private function escape(string $string) : string
    {
        return \htmlspecialchars($string, \ENT_XML1 | \ENT_COMPAT, 'UTF-8');
    }
}
