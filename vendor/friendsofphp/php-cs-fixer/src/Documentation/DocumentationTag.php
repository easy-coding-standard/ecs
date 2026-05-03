<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Documentation;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DocumentationTag
{
    /**
     * @var DocumentationTagType::*
     *
     * @readonly
     */
    public $type;
    /**
     * @readonly
     * @var string
     */
    public $title;
    /**
     * @readonly
     * @var string|null
     */
    public $description;
    /**
     * @param DocumentationTagType::* $type
     */
    public function __construct(string $type, string $title, ?string $description = null)
    {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
    }
}
