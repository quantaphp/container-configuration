<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Quanta\Container\Values\ArrayValue;
use Quanta\Container\Values\ValueInterface;

use Quanta\Exceptions\ArrayArgumentTypeErrorMessage;

describe('ArrayValue', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class);

    });

    context('when the array is empty', function () {

        beforeEach(function () {

            $this->value = new ArrayValue([]);

        });

        it('should implement ValueInterface', function () {

            expect($this->value)->toBeAnInstanceOf(ValueInterface::class);

        });

        describe('->value()', function () {

            it('should return an empty array', function () {

                $test = $this->value->value($this->container->get());

                expect($test)->toEqual([]);

            });

        });

        describe('->str()', function () {

            it('should return []', function () {

                $test = $this->value->str('container');

                expect($test)->toEqual('[]');

            });

        });

        describe('->strs()', function () {

            it('should return an empty array', function () {

                $test = $this->value->strs('container');

                expect($test)->toEqual([]);

            });

        });

    });

    context('when the array is not empty', function () {

        context('when all the array values are ValueInterface implementations', function () {

            beforeEach(function () {

                $this->value1 = mock(ValueInterface::class);
                $this->value2 = mock(ValueInterface::class);
                $this->value3 = mock(ValueInterface::class);

                $this->value1->value->with($this->container)->returns('value1');
                $this->value2->value->with($this->container)->returns('value2');
                $this->value3->value->with($this->container)->returns('value3');

                $this->value1->str->with('container')->returns('\'value1\'');
                $this->value2->str->with('container')->returns('\'value2\'');
                $this->value3->str->with('container')->returns('\'value3\'');

            });

            context('when the array has no string keys', function () {

                beforeEach(function () {

                    $this->value = new ArrayValue([
                        $this->value1->get(),
                        $this->value2->get(),
                        $this->value3->get(),
                    ]);

                });

                it('should implement ValueInterface', function () {

                    expect($this->value)->toBeAnInstanceOf(ValueInterface::class);

                });

                describe('->value()', function () {

                    it('should return the array of values returned by the ValueInterface implementations ->value() methods', function () {

                        $test = $this->value->value($this->container->get());

                        expect($test)->toEqual(['value1', 'value2', 'value3']);

                    });

                });

                describe('->str()', function () {

                    it('should return a string representation of the array of values returned by the ValueInterface implementations ->str() methods', function () {

                        $test = $this->value->str('container');

                        expect($test)->toEqual(<<<'EOT'
[
    'value1',
    'value2',
    'value3',
]
EOT
                        );

                    });

                });

                describe('->strs()', function () {

                    it('should return an array of values returned by the ValueInterface implementations ->str() methods', function () {

                        $test = $this->value->strs('container');

                        expect($test)->toEqual(['\'value1\'', '\'value2\'', '\'value3\'']);

                    });

                });

            });

            context('when the array has at least one string key', function () {

                beforeEach(function () {

                    $this->value = new ArrayValue([
                        'k1' => $this->value1->get(),
                        $this->value2->get(),
                        'k3' => $this->value3->get(),
                    ]);

                });

                it('should implement ValueInterface', function () {

                    expect($this->value)->toBeAnInstanceOf(ValueInterface::class);

                });

                describe('->value()', function () {

                    it('should return the array of values returned by the ValueInterface implementations ->value() methods', function () {

                        $test = $this->value->value($this->container->get());

                        expect($test)->toEqual([
                            'k1' => 'value1',
                            'value2',
                            'k3' => 'value3',
                        ]);

                    });

                });

                describe('->str()', function () {

                    it('should return a string representation of the array of values returned by the ValueInterface implementations ->str() methods', function () {

                        $test = $this->value->str('container');

                        expect($test)->toEqual(<<<'EOT'
[
    'k1' => 'value1',
    0 => 'value2',
    'k3' => 'value3',
]
EOT
                        );

                    });

                });

                describe('->strs()', function () {

                    it('should return an array of values returned by the ValueInterface implementations ->str() methods', function () {

                        $test = $this->value->strs('container');

                        expect($test)->toEqual([
                            'k1' => '\'value1\'',
                            '\'value2\'',
                            'k3' => '\'value3\'',
                        ]);

                    });

                });

            });

        });

        context('when a value of the array is not a ValueInterface implementation', function () {

            it('should throw an InvalidArgumentException', function () {

                ArrayArgumentTypeErrorMessage::testing();

                $values = [
                    mock(ValueInterface::class)->get(),
                    'value',
                    mock(ValueInterface::class)->get(),
                ];

                $test = function () use ($values) { new ArrayValue($values); };

                expect($test)->toThrow(new InvalidArgumentException(
                    (string) new ArrayArgumentTypeErrorMessage(1, ValueInterface::class, $values)
                ));

            });

        });

    });

});