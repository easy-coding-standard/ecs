<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\FileSystem;

use Nette\Utils\Strings;

/**
 * @api
 *
 * Used from
 * https://github.com/phpstan/phpstan-src/blob/02425e61aa48f0668b4efb3e73d52ad544048f65/src/File/FileHelper.php#L40,
 * with custom modifications
 *
 * @see \Symplify\EasyCodingStandard\Tests\FileSystem\PathNormalizerTest
 */
final class PathNormalizer
{
    /**
     * @see https://regex101.com/r/d4F5Fm/1
     */
    private const string SCHEME_PATH_REGEX = '#^([a-z]+)\\:\\/\\/(.+)#';

    /**
     * @see https://regex101.com/r/no28vw/1
     */
    private const string TWO_AND_MORE_SLASHES_REGEX = '#/{2,}#';

    private const string SCHEME_UNDEFINED = 'undefined';

    public function normalizePath(string $originalPath): string
    {
        $directorySeparator = DIRECTORY_SEPARATOR;

        $matches = Strings::match($originalPath, self::SCHEME_PATH_REGEX);
        if ($matches !== null) {
            [, $scheme, $path] = $matches;
        } else {
            $scheme = self::SCHEME_UNDEFINED;
            $path = $originalPath;
        }

        $normalizedPath = str_replace('\\', '/', (string) $path);
        $path = Strings::replace($normalizedPath, self::TWO_AND_MORE_SLASHES_REGEX, '/');

        $pathRoot = str_starts_with($path, '/') ? $directorySeparator : '';
        $pathParts = explode('/', trim($path, '/'));

        $normalizedPathParts = $this->normalizePathParts($pathParts, $scheme);

        $pathStart = ($scheme !== self::SCHEME_UNDEFINED ? $scheme . '://' : '');
        return $pathStart . $pathRoot . implode($directorySeparator, $normalizedPathParts);
    }

    /**
     * @param string[] $pathParts
     * @return string[]
     */
    private function normalizePathParts(array $pathParts, string $scheme): array
    {
        $normalizedPathParts = [];

        foreach ($pathParts as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }

            if ($pathPart !== '..') {
                $normalizedPathParts[] = $pathPart;
                continue;
            }

            /** @var string $removedPart */
            $removedPart = array_pop($normalizedPathParts);
            if ($scheme !== 'phar') {
                continue;
            }

            if (! \str_ends_with($removedPart, '.phar')) {
                continue;
            }

            $scheme = self::SCHEME_UNDEFINED;
        }

        return $normalizedPathParts;
    }
}
