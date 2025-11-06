<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Application;

use Symplify\EasyCodingStandard\Contract\Application\FileProcessorInterface;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\SniffRunner\Application\SniffFileProcessor;
final class FileProcessorCollector
{
    /**
     * @var FileProcessorInterface[]
     */
    private $fileProcessors = [];
    /**
     * orders matters, so Fixer can cleanup after Sniffer
     */
    public function __construct(SniffFileProcessor $sniffFileProcessor, FixerFileProcessor $fixerFileProcessor)
    {
        $this->fileProcessors[] = $sniffFileProcessor;
        $this->fileProcessors[] = $fixerFileProcessor;
    }
    /**
     * @return FileProcessorInterface[]
     */
    public function getFileProcessors() : array
    {
        return $this->fileProcessors;
    }
}
