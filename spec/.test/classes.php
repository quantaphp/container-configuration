<?php

namespace Test;

use Psr\Container\ContainerInterface;

use Quanta\Container\Metadata;
use Quanta\Container\Passes\ConfigurationPassInterface;

interface SomeInterface1 {}

interface SomeInterface2 {}

final class SomeClass1 implements SomeInterface1 {}
final class SomeClass2 implements SomeInterface1 {}
final class SomeClass3 implements SomeInterface1, SomeInterface2 {}
final class SomeClass4 implements SomeInterface1, SomeInterface2 {}
final class SomeClass5 implements SomeInterface2 {}

final class TestFactory
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function createStatic()
    {

    }

    public function create()
    {

    }

    public function __invoke(ContainerInterface $container)
    {
        //
    }
}

final class TestConfigurationPass implements ConfigurationPassInterface
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function factories(array $factories, Metadata $metadata): array
    {
        //
    }
}
