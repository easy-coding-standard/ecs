<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\DependencyInjection\CompilerPass;

use ECSPrefix202605\Illuminate\Container\Container;
use Symplify\EasyCodingStandard\DependencyInjection\SimpleParameterProvider;
use Symplify\EasyCodingStandard\ValueObject\Option;
final class RemoveExcludedCheckersCompilerPass
{
    public function process(Container $container) : void
    {
        $excludedCheckers = $this->getExcludedCheckersFromSkipParameter();
        foreach (\array_keys($container->getBindings()) as $classType) {
            if (!\in_array($classType, $excludedCheckers, \true)) {
                continue;
            }
            // remove service from container completely
            \Symplify\EasyCodingStandard\DependencyInjection\CompilerPass\CompilerPassHelper::removeCheckerFromContainer($container, $classType);
        }
    }
    /**
     * @return array<int, class-string>
     */
    private function getExcludedCheckersFromSkipParameter() : array
    {
        $excludedCheckers = [];
        $skip = SimpleParameterProvider::getArrayParameter(Option::SKIP);
        foreach ($skip as $key => $value) {
            $excludedChecker = $this->matchFullClassSkip($key, $value);
            if ($excludedChecker === null) {
                continue;
            }
            $excludedCheckers[] = $excludedChecker;
        }
        return \array_unique($excludedCheckers);
    }
    /**
     * @return class-string|null
     * @param int|string $key
     * @param mixed $value
     */
    private function matchFullClassSkip($key, $value) : ?string
    {
        // "SomeClass::class" => null
        if (\is_string($key) && \class_exists($key) && $value === null) {
            return $key;
        }
        // "SomeClass::class"
        if (!\is_int($key)) {
            return null;
        }
        if (!\is_string($value)) {
            return null;
        }
        if (!\class_exists($value)) {
            return null;
        }
        return $value;
    }
}
