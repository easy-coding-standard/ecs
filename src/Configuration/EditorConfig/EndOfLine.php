<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Configuration\EditorConfig;

final class EndOfLine
{
    /**
     * @var string
     */
    public const Posix = 'lf';
    /**
     * @var string
     */
    public const Legacy = 'cr';
    /**
     * @var string
     */
    public const Windows = 'crlf';
}
