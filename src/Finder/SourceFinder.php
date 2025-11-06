<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Finder;

use ECSPrefix202511\Symfony\Component\Finder\Finder;
use Symplify\EasyCodingStandard\DependencyInjection\SimpleParameterProvider;
use Symplify\EasyCodingStandard\ValueObject\Option;
use ECSPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Symplify\EasyCodingStandard\Tests\Finder\SourceFinderTest
 */
final class SourceFinder
{
    /**
     * @var string[]
     */
    private $fileExtensions = [];
    public function __construct()
    {
        $this->fileExtensions = SimpleParameterProvider::getArrayParameter(Option::FILE_EXTENSIONS);
    }
    /**
     * @param string[] $source
     * @return string[]
     */
    public function find(array $source) : array
    {
        $filePaths = [];
        foreach ($source as $singleSource) {
            if (\is_file($singleSource)) {
                $filePaths[] = $singleSource;
            } else {
                $filesInDirectory = $this->processDirectory($singleSource);
                $filePaths = \array_merge($filePaths, $filesInDirectory);
            }
        }
        \ksort($filePaths);
        return $filePaths;
    }
    /**
     * @return string[]
     */
    private function processDirectory(string $directory) : array
    {
        $normalizedFileExtensions = $this->normalizeFileExtensions($this->fileExtensions);
        $finder = Finder::create()->files()->ignoreDotFiles(\false)->name($normalizedFileExtensions)->in($directory)->exclude('vendor')->size('> 0')->sortByName();
        $filePaths = \array_keys(\iterator_to_array($finder));
        Assert::allString($filePaths);
        return $filePaths;
    }
    /**
     * @param string[] $fileExtensions
     * @return string[]
     */
    private function normalizeFileExtensions(array $fileExtensions) : array
    {
        $normalizedFileExtensions = [];
        foreach ($fileExtensions as $fileExtension) {
            $normalizedFileExtensions[] = '*.' . $fileExtension;
            $normalizedFileExtensions[] = '.*.' . $fileExtension;
        }
        return $normalizedFileExtensions;
    }
}
