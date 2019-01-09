<?php

use function Eloquent\Phony\Kahlan\mock;

use Interop\container\ServiceProviderInterface;

use Quanta\Utils\VendorDirectory;

use Quanta\Container\Configuration;
use Quanta\Container\ConfigurationFactory;
use Quanta\Container\PhpFileConfiguration;
use Quanta\Container\ClassNameCollectionConfiguration;

use Quanta\Container\Values\ValueFactoryInterface;

describe('ConfigurationFactory', function () {

    beforeEach(function () {

        $this->values = mock(ValueFactoryInterface::class);

        $this->factory = new ConfigurationFactory($this->values->get());

    });

    describe('->create()',function () {

        it('should return a new Configuration using the given service providers', function () {

            $providers = [
                mock(ServiceProviderInterface::class)->get(),
                mock(ServiceProviderInterface::class)->get(),
                mock(ServiceProviderInterface::class)->get(),
            ];

            $test = $this->factory->create(...$providers);

            expect($test)->toEqual(new Configuration(...$providers));

        });

    });

    describe('->files()', function () {

        it('should return a PhpFileConfiguration using the given glob patterns', function () {

            $patterns = ['pattern1', 'pattern2', 'pattern3'];

            $test = $this->factory->files(...$patterns);

            expect($test)->toEqual(new PhpFileConfiguration($this->values->get(), ...$patterns));

        });

    });

    describe('->vendor()', function () {

        beforeEach(function () {

            $this->collection = new VendorDirectory('path');

        });

        context('when no extra arguments are given', function () {

            it('should return a ClassNameCollectionConfiguration using a VendorDirectory using the given path', function () {

                $test = $this->factory->vendor('path');

                $expected = new ClassNameCollectionConfiguration($this->collection);

                expect($test)->toEqual($expected);

            });

        });

        context('when extra arguments are given', function () {

            it('should return a ClassNameCollectionConfiguration using the given extra arguments', function () {

                $test = $this->factory->vendor('path', 'pattern', 'bl1', 'bl2');

                $expected = new ClassNameCollectionConfiguration($this->collection, 'pattern', 'bl1', 'bl2');

                expect($test)->toEqual($expected);

            });

        });

    });

});
