<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\FileSystem;

use ECSPrefix202601\Nette\Utils\FileSystem;
use ECSPrefix202601\Nette\Utils\Json;
final class JsonFileSystem
{
    /**
     * @return array<string, mixed>
     */
    public static function readFilePath(string $filePath) : array
    {
        $fileContents = FileSystem::read($filePath);
        return Json::decode($fileContents, Json::FORCE_ARRAY);
    }
    /**
     * @param array<string, mixed> $data
     */
    public static function writeFilePath(string $filePath, array $data) : void
    {
        $jsonContents = Json::encode($data, Json::PRETTY);
        FileSystem::write($filePath, $jsonContents, null);
    }
}
