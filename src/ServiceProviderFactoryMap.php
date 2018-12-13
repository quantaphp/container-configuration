<?php declare(strict_types=1);

namespace Quanta\Container;

use Interop\Container\ServiceProviderInterface;

use Quanta\Exceptions\ReturnTypeErrorMessage;
use Quanta\Exceptions\ArrayReturnTypeErrorMessage;

final class ServiceProviderFactoryMap implements FactoryMapInterface
{
    /**
     * Service providers to merge as a factory map.
     *
     * @var \Interop\Container\ServiceProviderInterface[]
     */
    private $providers;

    /**
     * Constructor.
     *
     * @param \Interop\Container\ServiceProviderInterface ...$providers
     */
    public function __construct(ServiceProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    /**
     * @inheritdoc
     */
    public function factories(): array
    {
        $map = new ExtendedFactoryMap(
            new MergedFactoryMap(
                ...array_map([$this, 'factoryMap'], $this->providers)
            ),
            ...array_map([$this, 'extensionMap'], $this->providers)
        );

        return $map->factories();
    }

    /**
     * Return the factory map of the given service provider.
     *
     * @param \Interop\Container\ServiceProviderInterface $provider
     * @return \Quanta\Container\Factories\FactoryMap
     * @throws \UnexpectedValueException
     */
    private function factoryMap(ServiceProviderInterface $provider): FactoryMap
    {
        $factories = $provider->getFactories();

        if (! is_array($factories)) {
            throw new \UnexpectedValueException(
                (string) new ReturnTypeErrorMessage(
                    sprintf('%s::getFactories()', get_class($provider)),
                    'array',
                    $factories
                )
            );
        }

        try {
            return new FactoryMap($factories);
        }
        catch (\InvalidArgumentException $e) {
            throw new \UnexpectedValueException(
                (string) new ArrayReturnTypeErrorMessage(
                    sprintf('%s::getFactories()', get_class($provider)),
                    'callable',
                    $factories
                )
            );
        }
    }

    /**
     * Return the extension map of the given service provider.
     *
     * @param \Interop\Container\ServiceProviderInterface $provider
     * @return \Quanta\Container\Factories\FactoryMap
     * @throws \UnexpectedValueException
     */
    private function extensionMap(ServiceProviderInterface $provider): FactoryMap
    {
        $extensions = $provider->getExtensions();

        if (! is_array($extensions)) {
            throw new \UnexpectedValueException(
                (string) new ReturnTypeErrorMessage(
                    sprintf('%s::getExtensions()', get_class($provider)),
                    'array',
                    $extensions
                )
            );
        }

        try {
            return new FactoryMap($extensions);
        }
        catch (\InvalidArgumentException $e) {
            throw new \UnexpectedValueException(
                (string) new ArrayReturnTypeErrorMessage(
                    sprintf('%s::getExtensions()', get_class($provider)),
                    'callable',
                    $extensions
                )
            );
        }
    }

}