<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Reporter;

use ECSPrefix202511\Symfony\Component\Console\Style\SymfonyStyle;
final class CheckerListReporter
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @param string[] $checkerClasses
     */
    public function report(array $checkerClasses, string $type) : void
    {
        if ($checkerClasses === []) {
            return;
        }
        $sectionMessage = \sprintf('%d checker%s %s:', \count($checkerClasses), \count($checkerClasses) === 1 ? '' : 's', $type);
        $this->symfonyStyle->section($sectionMessage);
        $this->symfonyStyle->listing($checkerClasses);
    }
}
