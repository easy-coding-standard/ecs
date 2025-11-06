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
namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\ConfigurationException\UnresolvableAutoRuleSetConfigurationException;
use PhpCsFixer\RuleSet\AbstractRuleSetDefinition;
use PhpCsFixer\RuleSet\AutomaticRuleSetDefinitionInterface;
use PhpCsFixer\RuleSet\RuleSetDefinitionInterface;
/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @TODO refactor for DRY between Auto*Set classes // keradus
 */
final class AutoSet extends AbstractRuleSetDefinition implements AutomaticRuleSetDefinitionInterface
{
    public function getName() : string
    {
        return '@' . \lcfirst(\ltrim(parent::getName(), '@'));
    }
    public function getRules() : array
    {
        $sets = \array_filter($this->getCandidates(), function (RuleSetDefinitionInterface $set) : bool {
            return $this->isSetDiscoverable($set);
        });
        $sets = \array_map(static function (RuleSetDefinitionInterface $set) : string {
            return $set->getName();
        }, $sets);
        return \array_combine($sets, \array_fill(0, \count($sets), \true));
    }
    public function getDescription() : string
    {
        return 'Default rule set. Applies newest PER-CS and optimizations for PHP, based on project\'s "composer.json" file.';
    }
    public function getRulesCandidates() : array
    {
        $sets = \array_map(static function (RuleSetDefinitionInterface $set) : string {
            return $set->getName();
        }, $this->getCandidates());
        return \array_combine($sets, \array_fill(0, \count($sets), \true));
    }
    /** @return list<RuleSetDefinitionInterface> */
    private function getCandidates() : array
    {
        // order matters
        return [new \PhpCsFixer\RuleSet\Sets\PERCSSet(), new \PhpCsFixer\RuleSet\Sets\AutoPHPMigrationSet()];
    }
    private function isSetDiscoverable(RuleSetDefinitionInterface $set) : bool
    {
        try {
            $set->getRules();
            return \true;
        } catch (UnresolvableAutoRuleSetConfigurationException $unused) {
            return \false;
        }
    }
}
