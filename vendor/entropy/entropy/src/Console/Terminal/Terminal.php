<?php

declare (strict_types=1);
namespace ECSPrefix202605\Entropy\Console\Terminal;

final class Terminal
{
    public static function padVisibleRight(string $text, int $width, string $padChar = ' ') : string
    {
        $len = self::visibleLength($text);
        if ($len >= $width) {
            return $text;
        }
        return $text . \str_repeat($padChar, $width - $len);
    }
    private static function visibleLength(string $text) : int
    {
        // remove console meta tags like <fg=green> ... </> and <bg=red> ... </>
        $stripped = \preg_replace('#</?>|<(?:fg|bg)=(?:green|yellow|red|cyan|orange)>#', '', $text);
        $stripped = $stripped ?? $text;
        return \strlen($stripped);
    }
}
