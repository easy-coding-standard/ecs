<?php

declare (strict_types=1);
namespace ECSPrefix202511;

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
use ECSPrefix202511\ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ECSPrefix202511\ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;
return (new Configuration())->disableExtensionsAnalysis()->ignoreErrorsOnPackage('composer/xdebug-handler', [ErrorType::UNUSED_DEPENDENCY])->ignoreErrorsOnPackage('symfony/polyfill-mbstring', [ErrorType::UNUSED_DEPENDENCY])->ignoreErrorsOnPackage('symfony/polyfill-php80', [ErrorType::UNUSED_DEPENDENCY])->ignoreErrorsOnPackage('symfony/polyfill-php81', [ErrorType::UNUSED_DEPENDENCY])->ignoreErrorsOnPackage('symfony/polyfill-php84', [ErrorType::UNUSED_DEPENDENCY])->ignoreErrorsOnPackage('symfony/event-dispatcher-contracts', [ErrorType::SHADOW_DEPENDENCY])->ignoreErrorsOnPath('dev-tools', [ErrorType::UNKNOWN_CLASS, ErrorType::SHADOW_DEPENDENCY])->ignoreErrorsOnPath('tests', [ErrorType::UNKNOWN_CLASS])->ignoreUnknownClasses(['T_PIPE', 'T_VOID_CAST']);
