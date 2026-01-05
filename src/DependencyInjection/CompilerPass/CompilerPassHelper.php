<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\DependencyInjection\CompilerPass;

use ECSPrefix202601\Illuminate\Container\Container;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\FixerInterface;
use Symplify\EasyCodingStandard\Utils\PrivatesAccessorHelper;
final class CompilerPassHelper
{
    /**
     * @return string[]
     */
    public static function resolveCheckerClasses(Container $container) : array
    {
        $serviceTypes = \array_keys($container->getBindings());
        return \array_filter($serviceTypes, static function (string $serviceType) : bool {
            if (\is_a($serviceType, FixerInterface::class, \true)) {
                return \true;
            }
            return \is_a($serviceType, Sniff::class, \true);
        });
    }
    public static function removeCheckerFromContainer(COntainer $container, string $checkerClass) : void
    {
        // remove instance
        $container->offsetUnset($checkerClass);
        $tags = PrivatesAccessorHelper::getPropertyValue($container, 'tags');
        // nothing to remove
        if ($tags === []) {
            return;
        }
        // remove from tags
        $checkerTagClasses = [FixerInterface::class, Sniff::class];
        foreach ($checkerTagClasses as $checkerTagClass) {
            foreach ($tags[$checkerTagClass] ?? [] as $key => $class) {
                if ($class !== $checkerClass) {
                    continue;
                }
                unset($tags[$checkerTagClass][$key]);
            }
        }
        // update value
        PrivatesAccessorHelper::setPropertyValue($container, 'tags', $tags);
    }
}
