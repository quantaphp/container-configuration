<?php

use Quanta\Container\EnvVar;
use Quanta\Container\Parsing\EnvVarParser;
use Quanta\Container\Parsing\ParsedFactory;
use Quanta\Container\Parsing\ParsingFailure;
use Quanta\Container\Parsing\ParserInterface;

describe('EnvVarParser', function () {

    beforeEach(function () {

        $this->parser = new EnvVarParser;

    });

    it('should implement ParserInterface', function () {

        expect($this->parser)->toBeAnInstanceOf(ParserInterface::class);

    });

    describe('->__invoke()', function () {

        context('when the given value is not a string', function () {

            it('should return a ParsingFailure', function () {

                $test = ($this->parser)([]);

                expect($test)->toEqual(new ParsingFailure([]));

            });

        });

        context('when the given value is a string', function () {

            context('when the given string is formatted as env()', function () {

                context('when there is no argument', function () {

                    it('should return a ParsingFailure', function () {

                        $test = ($this->parser)('env()');

                        expect($test)->toEqual(new ParsingFailure('env()'));

                    });

                });

                context('when there is one argument', function () {

                    it('should return a ParsedFactory wrapped around an EnvVar', function () {

                        $test = ($this->parser)('env(NAME)');

                        expect($test)->toEqual(new ParsedFactory(
                            new EnvVar('NAME')
                        ));

                    });

                });

                context('when there is two arguments', function () {

                    it('should return a ParsedFactory wrapped around an EnvVar', function () {

                        $test = ($this->parser)('env(NAME, default)');

                        expect($test)->toEqual(new ParsedFactory(
                            new EnvVar('NAME', 'default')
                        ));

                    });

                });

                context('when there is three arguments', function () {

                    it('should return a ParsedFactory wrapped around an EnvVar', function () {

                        $test = ($this->parser)('env(NAME, default, int)');

                        expect($test)->toEqual(new ParsedFactory(
                            new EnvVar('NAME', 'default', 'int')
                        ));

                    });

                });

                context('when there more than three arguments', function () {

                    it('should return a ParsingFailure', function () {

                        $test = ($this->parser)('env(NAME, default, int, x)');

                        expect($test)->toEqual(new ParsingFailure('env(NAME, default, int, x)'));

                    });

                });

            });

            context('when the given string is not formatted as env()', function () {

                it('should return a ParsingFailure', function () {

                    $test = ($this->parser)('value');

                    expect($test)->toEqual(new ParsingFailure('value'));

                });

            });

        });

    });

});
