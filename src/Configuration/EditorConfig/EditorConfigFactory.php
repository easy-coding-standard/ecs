<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Configuration\EditorConfig;

use Exception;
class EditorConfigFactory
{
    public function load() : \Symplify\EasyCodingStandard\Configuration\EditorConfig\EditorConfig
    {
        // By default, composer executes scripts from within the project root.
        $projectRoot = \getcwd();
        $editorConfigPath = $projectRoot . '/.editorconfig';
        if (!\file_exists($editorConfigPath)) {
            throw new Exception('No .editorconfig found.');
        }
        $configFileContent = \file_get_contents($editorConfigPath);
        if ($configFileContent === \false) {
            throw new Exception('Unable to load .editorconfig.');
        }
        return $this->parse($configFileContent);
    }
    public function parse(string $editorConfigFileContents) : \Symplify\EasyCodingStandard\Configuration\EditorConfig\EditorConfig
    {
        $fullConfig = \parse_ini_string($editorConfigFileContents, \true, \INI_SCANNER_TYPED);
        if ($fullConfig === \false) {
            throw new Exception('Unable to parse .editorconfig.');
        }
        $config = \array_merge(\is_array($fullConfig['*'] ?? []) ? $fullConfig['*'] ?? [] : \iterator_to_array($fullConfig['*'] ?? []), \is_array($fullConfig['*.php'] ?? []) ? $fullConfig['*.php'] ?? [] : \iterator_to_array($fullConfig['*.php'] ?? []));
        // Just letting "validation" happen with PHP's type hints.
        return new \Symplify\EasyCodingStandard\Configuration\EditorConfig\EditorConfig($config['indent_style'] ?? null, $config['end_of_line'] ?? null, $this->field($config, 'trim_trailing_whitespace', \Closure::fromCallable([$this, 'id'])), $this->field($config, 'insert_final_newline', \Closure::fromCallable([$this, 'id'])), $this->field($config, 'max_line_length', \Closure::fromCallable([$this, 'id'])), $config['quote_type'] ?? null);
    }
    /**
     * @template From
     * @template To
     * @param mixed[] $config
     * @param callable(From): To $transform
     * @return To|null
     */
    private function field(array $config, string $field, callable $transform)
    {
        if (!isset($config[$field])) {
            return null;
        }
        return $transform($config[$field]);
    }
    /**
     * @template T
     * @param mixed $value
     * @return T
     */
    private function id($value)
    {
        return $value;
    }
}
