<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\ValueObject\Set;

/**
 * @api
 */
final class SetList
{
    /**
     * @api
     * @var string
     */
    public const PSR_12 = __DIR__ . '/../../../config/set/psr12.php';
    /**
     * @api
     * @var string
     */
    public const CLEAN_CODE = __DIR__ . '/../../../config/set/clean-code.php';
    /**
     * @api
     * @var string
     */
    public const SYMPLIFY = __DIR__ . '/../../../config/set/symplify.php';
    /**
     * @api
     * @var string
     */
    public const ARRAY = __DIR__ . '/../../../config/set/common/array.php';
    /**
     * @api
     * @var string
     */
    public const COMMON = __DIR__ . '/../../../config/set/common.php';
    /**
     * @api
     * @var string
     */
    public const COMMENTS = __DIR__ . '/../../../config/set/common/comments.php';
    /**
     * @api
     * @var string
     */
    public const CONTROL_STRUCTURES = __DIR__ . '/../../../config/set/common/control-structures.php';
    /**
     * @api
     * @var string
     */
    public const DOCBLOCK = __DIR__ . '/../../../config/set/common/docblock.php';
    /**
     * @api
     * @var string
     */
    public const NAMESPACES = __DIR__ . '/../../../config/set/common/namespaces.php';
    /**
     * @api
     * @deprecated as dangerous without context. Use Rector instead.
     * @var string
     */
    public const PHPUNIT = __DIR__ . '/../../../config/set/common/phpunit.php';
    /**
     * @api
     * @var string
     */
    public const SPACES = __DIR__ . '/../../../config/set/common/spaces.php';
    /**
     * @api
     * @deprecated as dangerous without context. Use Rector instead.
     * @var string
     */
    public const STRICT = __DIR__ . '/../../../config/set/common/strict.php';
    /**
     * @api
     * @var string
     */
    public const DOCTRINE_ANNOTATIONS = __DIR__ . '/../../../config/set/doctrine-annotations.php';
    /**
     * @api
     * @var string
     */
    public const LARAVEL = __DIR__ . '/../../../config/set/laravel.php';
}
