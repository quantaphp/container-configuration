<?php

use function Eloquent\Phony\Kahlan\mock;

use Quanta\Container\Alias;
use Quanta\Container\Tagging;
use Quanta\Container\Invokable;
use Quanta\Container\FactoryMap;
use Quanta\Container\TaggingPass;
use Quanta\Container\ExtensionPass;
use Quanta\Container\MergedFactoryMap;
use Quanta\Container\ParameterFactoryMap;
use Quanta\Container\MergedProcessingPass;
use Quanta\Container\ProcessingPassInterface;
use Quanta\Container\Parsing\ParserInterface;
use Quanta\Container\Configuration\ArrayConfigurationUnit;
use Quanta\Container\Configuration\ConfigurationUnitInterface;

describe('ArrayConfigurationUnit::instance()', function () {

    beforeEach(function () {

        $this->parser = mock(ParserInterface::class);

    });

    context('when no source is given', function () {

        it('should return a new ArrayConfigurationUnit with the given parser, configuration array and an empty source', function () {

            $test = ArrayConfigurationUnit::instance($this->parser->get(), [
                'key' => 'value',
            ]);

            expect($test)->toEqual(new ArrayConfigurationUnit($this->parser->get(), [
                'key' => 'value',
            ], ''));

        });

    });

    context('when a source is given', function () {

        it('should return a new ArrayConfigurationUnit with the given parser, configuration array and source', function () {

            $test = ArrayConfigurationUnit::instance($this->parser->get(), [
                'key' => 'value',
            ], 'source');

            expect($test)->toEqual(new ArrayConfigurationUnit($this->parser->get(), [
                'key' => 'value',
            ], 'source'));

        });

    });

});

describe('ArrayConfigurationUnit', function () {

    beforeEach(function () {

        $this->parser = mock(ParserInterface::class);

    });

    context('when the configuration array is valid', function () {

        beforeEach(function () {

            $this->unit = new ArrayConfigurationUnit($this->parser->get(), [
                'parameters' => [
                    'id1' => 'parameter1',
                    'id2' => 'parameter2',
                    'id3' => 'parameter3',
                ],
                'aliases' => [
                    'id1' => 'alias1',
                    'id2' => 'alias2',
                    'id3' => 'alias3',
                ],
                'invokables' => [
                    'id1' => Test\SomeInvokable1::class,
                    'id2' => Test\SomeInvokable2::class,
                    'id3' => Test\SomeInvokable3::class,
                ],
                'factories' => [
                    'id1' => $this->factory1 = function () {},
                    'id2' => $this->factory2 = function () {},
                    'id3' => $this->factory3 = function () {},
                ],
                'tags' => [
                    'id1' => ['tag11', 'no error' => 'tag12', 'tag13'],
                    'id2' => ['tag21', 'no error' => 'tag22', 'tag23'],
                    'id3' => ['tag31', 'no error' => 'tag32', 'tag33'],
                ],
                'mappers' => [
                    'id1' => Test\SomeInterface1::class,
                    'id2' => Test\SomeInterface2::class,
                    'id3' => Test\SomeInterface3::class,
                ],
                'extensions' => [
                    'id1' => $this->extension1 = function () {},
                    'id2' => $this->extension2 = function () {},
                    'id3' => $this->extension3 = function () {},
                ],
                'passes' => [
                    $this->pass1 = mock(ProcessingPassInterface::class)->get(),
                    'no error' => $this->pass2 = mock(ProcessingPassInterface::class)->get(),
                    $this->pass3 = mock(ProcessingPassInterface::class)->get(),
                ],
                'key' => 'extra keys are ignored without errors',
            ]);

        });

        it('should implement ConfigurationUnitInterface', function () {

            expect($this->unit)->toBeAnInstanceOf(ConfigurationUnitInterface::class);

        });

        describe('->map()', function () {

            it('should return a factory map', function () {

                $test = $this->unit->map();

                expect($test)->toEqual(new MergedFactoryMap(...[
                    new ParameterFactoryMap($this->parser->get(), [
                        'id1' => 'parameter1',
                        'id2' => 'parameter2',
                        'id3' => 'parameter3',
                    ]),
                    new FactoryMap([
                        'id1' => new Alias('alias1'),
                        'id2' => new Alias('alias2'),
                        'id3' => new Alias('alias3'),
                    ]),
                    new FactoryMap([
                        'id1' => new Invokable(Test\SomeInvokable1::class),
                        'id2' => new Invokable(Test\SomeInvokable2::class),
                        'id3' => new Invokable(Test\SomeInvokable3::class),
                    ]),
                    new FactoryMap([
                        'id1' => $this->factory1,
                        'id2' => $this->factory2,
                        'id3' => $this->factory3,
                    ]),
                ]));

            });

        });

        describe('->pass()', function () {

            it('should return a processing pass', function () {

                $test = $this->unit->pass();

                expect($test)->toEqual(new MergedProcessingPass(...[
                    new TaggingPass('id1', new Tagging\Entries('tag11', 'tag12', 'tag13')),
                    new TaggingPass('id2', new Tagging\Entries('tag21', 'tag22', 'tag23')),
                    new TaggingPass('id3', new Tagging\Entries('tag31', 'tag32', 'tag33')),
                    new TaggingPass('id1', new Tagging\Implementations(Test\SomeInterface1::class)),
                    new TaggingPass('id2', new Tagging\Implementations(Test\SomeInterface2::class)),
                    new TaggingPass('id3', new Tagging\Implementations(Test\SomeInterface3::class)),
                    new ExtensionPass('id1', $this->extension1),
                    new ExtensionPass('id2', $this->extension2),
                    new ExtensionPass('id3', $this->extension3),
                    $this->pass1,
                    $this->pass2,
                    $this->pass3,
                ]));

            });

        });

    });

    context('when the configuration array is not valid', function () {

        describe('->map()', function () {

            context('when the parameter key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'parameters' => 1,
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'parameters' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when the aliases key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'aliases' => 1,
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'aliases' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when an alias is not a string', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'aliases' => [
                                'id1' => 'alias1',
                                'id2' => [],
                                'id3' => 'alias3',
                            ],
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'aliases' => [
                                'id1' => 'alias1',
                                'id2' => [],
                                'id3' => 'alias3',
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when the invokables key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'invokables' => 1,
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'invokables' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when an invokable is not a string', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'invokables' => [
                                'id1' => Test\SomeInvokable1::class,
                                'id2' => [],
                                'id3' => Test\SomeInvokable3::class,
                            ],
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'invokables' => [
                                'id1' => Test\SomeInvokable1::class,
                                'id2' => [],
                                'id3' => Test\SomeInvokable3::class,
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when the factories key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'factories' => 1,
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'factories' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when a factory is not a callable', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'factories' => [
                                'id1' => function () {},
                                'id2' => 1,
                                'id3' => function () {},
                            ],
                        ]);

                        expect([$unit, 'map'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'factories' => [
                                'id1' => function () {},
                                'id2' => 1,
                                'id3' => function () {},
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->map(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

        });

        describe('->pass()', function () {

            context('when the tags key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'tags' => 1,
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'tags' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when a tag is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'tags' => [
                                'id1' => [],
                                'id2' => 1,
                                'id3' => [],
                            ],
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'tags' => [
                                'id1' => [],
                                'id2' => 1,
                                'id3' => [],
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when a tag is not an array of strings', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'tags' => [
                                'id1' => ['tag11', 'tag12', 'tag13'],
                                'id2' => ['tag21', [], 'tag23'],
                                'id3' => ['tag31', 'tag32', 'tag33'],
                            ],
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'tags' => [
                                'id1' => ['tag11', 'tag12', 'tag13'],
                                'id2' => ['tag21', [], 'tag23'],
                                'id3' => ['tag31', 'tag32', 'tag33'],
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when the mappers key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'mappers' => 1,
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'mappers' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when a mapper is not a string', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'mappers' => [
                                'id1' => Test\SomeInterface1::class,
                                'id2' => [],
                                'id3' => Test\SomeInterface3::class,
                            ],
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'mappers' => [
                                'id1' => Test\SomeInterface1::class,
                                'id2' => [],
                                'id3' => Test\SomeInterface3::class,
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when the extensions key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'extensions' => 1,
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'extensions' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when an extension is not a callable', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'extensions' => [
                                'id1' => function () {},
                                'id2' => 1,
                                'id3' => function () {},
                            ],
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'extensions' => [
                                'id1' => function () {},
                                'id2' => 1,
                                'id3' => function () {},
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when the passes key is not an array', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'passes' => 1,
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'passes' => 1,
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

            context('when a pass is not a ProcessingPassInterface implementation', function () {

                context('when there is no source', function () {

                    it('should throw an unexpected value exception', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'passes' => [
                                'id1' => mock(ProcessingPassInterface::class)->get(),
                                'id2' => Test\SomeClass::class,
                                'id3' => mock(ProcessingPassInterface::class)->get(),
                            ],
                        ]);

                        expect([$unit, 'pass'])->toThrow(new UnexpectedValueException);

                    });

                });

                context('when there is a source', function () {

                    it('should throw an unexpected value exception displaying the source', function () {

                        $unit = new ArrayConfigurationUnit($this->parser->get(), [
                            'passes' => [
                                'id1' => mock(ProcessingPassInterface::class)->get(),
                                'id2' => Test\SomeClass::class,
                                'id3' => mock(ProcessingPassInterface::class)->get(),
                            ],
                        ], 'source');

                        $test = '';

                        try { $unit->pass(); }

                        catch (UnexpectedValueException $e) {
                            $test = $e->getMessage();
                        }

                        expect($test)->toContain('(source)');

                    });

                });

            });

        });

    });

});
