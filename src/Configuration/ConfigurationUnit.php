<?php declare(strict_types=1);

namespace Quanta\Container\Configuration;

use Quanta\Container\FactoryMapInterface;
use Quanta\Container\MergedProcessingPass;
use Quanta\Container\ProcessingPassInterface;

final class ConfigurationUnit implements ConfigurationUnitInterface
{
    /**
     * The factory map.
     *
     * @var \Quanta\Container\FactoryMapInterface
     */
    private $map;

    /**
     * The array of processing passes.
     *
     * @var \Quanta\Container\ProcessingPassInterface[]
     */
    private $passes;

    /**
     * Constructor.
     *
     * @param \Quanta\Container\FactoryMapInterface     $map
     * @param \Quanta\Container\ProcessingPassInterface ...$passes
     */
    public function __construct(FactoryMapInterface $map, ProcessingPassInterface ...$passes)
    {
        $this->map = $map;
        $this->passes = $passes;
    }

    /**
     * @inheritdoc
     */
    public function map(): FactoryMapInterface
    {
        return $this->map;
    }

    /**
     * @inheritdoc
     */
    public function pass(): ProcessingPassInterface
    {
        return new MergedProcessingPass(...$this->passes);
    }
}
