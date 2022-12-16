<?php

/*
 * This file is part of the zenstruck/callback package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Callback\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Callback;
use Zenstruck\Callback\Argument;
use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackTest extends TestCase
{
    /**
     * @test
     */
    public function create_must_be_callable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Callback::createFor('not a callable');
    }

    /**
     * @test
     */
    public function invoke_all_can_enforce_min_arguments(): void
    {
        $callback = Callback::createFor(fn() => 'ret');

        $this->expectException(\ArgumentCountError::class);

        $callback->invokeAll(Parameter::untyped('foo'), 1);
    }

    /**
     * @test
     */
    public function invoke_all_with_no_arguments(): void
    {
        $actual = Callback::createFor(fn() => 'ret')
            ->invokeAll(Parameter::untyped('foo'))
        ;

        $this->assertSame('ret', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_with_string_callable(): void
    {
        $actual = Callback::createFor('strtoupper')
            ->invokeAll(Parameter::union(
                Parameter::untyped('foobar'),
                Parameter::typed('string', 'foobar')
            )
            )
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_untyped_argument(): void
    {
        $actual = Callback::createFor(fn($string) => \mb_strtoupper($string))
            ->invokeAll(Parameter::untyped('foobar'))
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_primitive_typed_argument(): void
    {
        $actual = Callback::createFor(fn(string $string) => \mb_strtoupper($string))
            ->invokeAll(Parameter::typed('string', 'foobar'))
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_class_arguments(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1, Object2 $object2, $object3) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
            ];
        };

        $actual = Callback::createFor($function)
            ->invokeAll(Parameter::union(
                Parameter::untyped($object),
                Parameter::typed(Object1::class, $object)
            ))
        ;

        $this->assertSame(
            [
                'object1' => $object,
                'object2' => $object,
                'object3' => $object,
            ],
            $actual
        );
    }

    /**
     * @test
     */
    public function invoke_all_class_arguments_value_factories(): void
    {
        $function = static function(Object1 $object1, Object2 $object2, $object3) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
            ];
        };
        $factoryArgs = [];
        $factory = Parameter::factory(static function($arg) use (&$factoryArgs) {
            $factoryArgs[] = $arg;

            if ($arg) {
                return new $arg();
            }

            return new Object1();
        });

        $ret = Callback::createFor($function)
            ->invokeAll(Parameter::union(
                Parameter::untyped($factory),
                Parameter::typed(Object1::class, $factory)
            ))
        ;

        $this->assertSame(['object1', 'object2', 'object3'], \array_keys($ret));
        $this->assertInstanceOf(Object1::class, $ret['object1']);
        $this->assertInstanceOf(Object2::class, $ret['object2']);
        $this->assertInstanceOf(Object1::class, $ret['object3']);
        $this->assertSame(
            [Object1::class, Object2::class, null],
            $factoryArgs
        );
    }

    /**
     * @test
     */
    public function invoke_all_unresolvable_parameter(): void
    {
        $callback = Callback::createFor(static function(Object1 $object1, Object2 $object2, Object3 $object3) {});

        $this->expectException(UnresolveableArgument::class);
        $this->expectExceptionMessage('Unable to resolve argument 2 for callback. Expected type: "mixed|Zenstruck\Callback\Tests\Object1"');

        $callback->invokeAll(Parameter::union(
            Parameter::untyped(new Object1()),
            Parameter::typed(Object1::class, new Object1())
        ));
    }

    /**
     * @test
     */
    public function invoke_with_no_args(): void
    {
        $actual = Callback::createFor(fn() => 'ret')->invoke();

        $this->assertSame('ret', $actual);
    }

    /**
     * @test
     */
    public function invoke_with_too_few_parameters(): void
    {
        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage('Too few arguments passed to "Zenstruck\Callback\Tests\CallbackTest');
        $this->expectExceptionMessage('Expected 2, got 1.');

        Callback::createFor(fn(string $string, float $float, ?int $int = null) => 'ret')->invoke('2');
    }

    /**
     * @test
     */
    public function invoke_with_non_parameters(): void
    {
        $callback = Callback::createFor(
            fn(string $string, float $float, ?int $int = null) => [$string, $float, $int]
        );

        $this->assertSame(['value', 3.4, null], $callback->invoke('value', 3.4));
    }

    /**
     * @test
     */
    public function invoke_with_resolvable_args(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1, Object2 $object2, $object3, $extra) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
                'extra' => $extra,
            ];
        };

        $actual = Callback::createFor($function)
            ->invoke(
                Parameter::typed(Object1::class, $object),
                Parameter::typed(Object2::class, $object),
                Parameter::untyped($object),
                'value'
            )
        ;

        $this->assertSame(
            [
                'object1' => $object,
                'object2' => $object,
                'object3' => $object,
                'extra' => 'value',
            ],
            $actual
        );
    }

    /**
     * @test
     */
    public function invoke_with_unresolvable_argument(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1, $object2, $object3, $extra) {};

        $this->expectException(UnresolveableArgument::class);
        $this->expectExceptionMessage('Unable to resolve argument 2 for callback. Expected type: "Zenstruck\Callback\Tests\Object2"');

        Callback::createFor($function)
            ->invoke(
                Parameter::typed(Object1::class, $object),
                Parameter::typed(Object2::class, $object),
                Parameter::untyped($object),
                'value'
            )
        ;
    }

    /**
     * @test
     */
    public function invoke_with_not_enough_required_arguments(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1) {};

        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage('No argument 2 for callable. Expected type: "Zenstruck\Callback\Tests\Object2"');

        Callback::createFor($function)
            ->invoke(
                Parameter::typed(Object1::class, $object),
                Parameter::typed(Object2::class, $object),
                Parameter::untyped($object),
                'value'
            )
        ;
    }

    /**
     * @test
     */
    public function can_mark_invoke_parameter_arguments_as_optional(): void
    {
        $actual = Callback::createFor(static fn() => 'ret')
            ->invoke(Parameter::typed('string', 'foobar')->optional())
        ;

        $this->assertSame('ret', $actual);

        $actual = Callback::createFor(static fn(string $v) => $v)
            ->invoke(Parameter::typed('string', 'foobar')->optional())
        ;

        $this->assertSame('foobar', $actual);
    }

    /**
     * @test
     */
    public function is_stringable(): void
    {
        $this->assertSame('(function) strlen()', (string) Callback::createFor('strlen'));
        $this->assertStringMatchesFormat('(closure) '.__FILE__.':%d', (string) Callback::createFor(function() {}));
        $this->assertStringMatchesFormat('(closure) '.__FILE__.':%d', (string) Callback::createFor([$this, 'is_stringable']));
        $this->assertStringMatchesFormat('(closure) '.__FILE__.':%d', (string) Callback::createFor(new Object4()));
        $this->assertStringMatchesFormat('(closure) '.__FILE__.':%d', (string) Callback::createFor([Object4::class, 'staticMethod']));
        $this->assertSame('(function) '.__NAMESPACE__.'\test_function()', (string) Callback::createFor(__NAMESPACE__.'\test_function'));
    }

    /**
     * @test
     */
    public function invoke_can_support_union_typehints(): void
    {
        $callback = fn(Object1|string $arg) => 'ret';

        $this->assertSame('ret', Callback::createFor($callback)->invokeAll(Parameter::typed(Object1::class, new Object1())));
        $this->assertSame('ret', Callback::createFor($callback)->invokeAll(Parameter::typed('string', 'value')));
        $this->assertSame('ret', Callback::createFor($callback)->invoke(Parameter::typed(Object1::class, new Object1())));
        $this->assertSame('ret', Callback::createFor($callback)->invoke(Parameter::typed('string', 'value')));
    }

    /**
     * @test
     */
    public function can_get_callback_arguments(): void
    {
        $callback = Callback::createFor(function(Object1 $a, $b, string $c) {});

        $this->assertSame(Object1::class, $callback->argument(0)->type());
        $this->assertNull($callback->argument(1)->type());
        $this->assertSame('string', $callback->argument(2)->type());
        $this->assertSame(
            [
                Object1::class,
                null,
                'string',
            ],
            \array_map(fn(Argument $a) => $a->type(), $callback->arguments())
        );
    }

    /**
     * @test
     */
    public function can_get_union_callback_arguments(): void
    {
        $callback = fn(Object1|string $a, $b, string $c) => null;
        $callback = Callback::createFor($callback);

        $this->assertSame(Object1::class.'|string', $callback->argument(0)->type());
        $this->assertNull($callback->argument(1)->type());
        $this->assertSame('string', $callback->argument(2)->type());
        $this->assertSame(
            [
                Object1::class.'|string',
                null,
                'string',
            ],
            \array_map(fn(Argument $a) => $a->type(), $callback->arguments())
        );
    }

    /**
     * @test
     */
    public function exception_thrown_when_trying_to_access_invalid_argument(): void
    {
        $this->expectException(\OutOfRangeException::class);

        Callback::createFor(function() {})->argument(0);
    }

    /**
     * @test
     */
    public function value_factory_injects_argument_if_type_hinted(): void
    {
        $callback = Callback::createFor(fn(string $a, int $b, $c) => [$a, $b, $c]);
        $factory = Parameter::factory(function(Argument $argument) {
            if ($argument->supports('string', Argument::STRICT)) {
                return 'string';
            }

            if ($argument->supports('int')) {
                return 17;
            }

            return 'invalid';
        });

        $ret = $callback->invokeAll(
            Parameter::union(
                Parameter::typed('string', $factory),
                Parameter::typed('int', $factory),
                Parameter::untyped($factory)
            )
        );

        $this->assertSame(['string', 17, 'string'], $ret);
    }

    /**
     * @test
     */
    public function can_use_value_factory_with_no_argument(): void
    {
        $ret = Callback::createFor(fn($value) => $value)
            ->invoke(Parameter::untyped(Parameter::factory(fn() => 'value')))
        ;

        $this->assertSame('value', $ret);
    }

    /**
     * @test
     */
    public function value_factory_can_be_used_with_union_arguments_if_no_value_factory_argument(): void
    {
        $callback = fn(Object1|string $a) => $a;

        $ret = Callback::createFor($callback)
            ->invoke(Parameter::typed('string', Parameter::factory(fn() => 'value')))
        ;

        $this->assertSame('value', $ret);
    }

    /**
     * @test
     */
    public function value_factory_can_be_used_with_union_arguments_as_array(): void
    {
        $array = [];
        $factory = Parameter::factory(function(array $types) use (&$array) {
            $array = $types;

            return 'value';
        });

        $callback = fn(Object1|string $a) => $a;
        $ret = Callback::createFor($callback)
            ->invoke(Parameter::typed('string', $factory))
        ;

        $this->assertSame('value', $ret);
        $this->assertSame([Object1::class, 'string'], $array);
    }

    /**
     * @test
     */
    public function value_factory_cannot_accept_union_argument(): void
    {
        $this->expectException(\LogicException::class);

        $callback = fn(Object1|string $a) => $a;

        Callback::createFor($callback)
            ->invoke(Parameter::typed('string', Parameter::factory(fn(string $type) => $type)))
        ;
    }

    /**
     * @test
     */
    public function argument_supports(): void
    {
        $callback1 = Callback::createFor(function(?Object1 $object, string $string, int $int, $noType, float $float, bool $bool) {});
        $callback2 = Callback::createFor(function(Object2 $object, string $string, $noType) {});

        $this->assertTrue($callback1->argument(0)->supports(Object1::class));
        $this->assertTrue($callback1->argument(0)->supports(Object2::class));
        $this->assertTrue($callback1->argument(0)->supports('null'));
        $this->assertTrue($callback1->argument(0)->supports('NULL'));
        $this->assertFalse($callback1->argument(0)->supports('string'));
        $this->assertFalse($callback1->argument(0)->supports(Object3::class));
        $this->assertFalse($callback1->argument(0)->supports(Object2::class, Argument::CONTRAVARIANCE));
        $this->assertFalse($callback1->argument(0)->supports(Object2::class, Argument::EXACT));
        $this->assertTrue($callback1->argument(0)->supports(Object1::class, Argument::EXACT));
        $this->assertTrue($callback1->argument(0)->supports('null', Argument::EXACT));

        $this->assertTrue($callback1->argument(1)->supports('string'));
        $this->assertTrue($callback1->argument(1)->supports('int'));
        $this->assertTrue($callback1->argument(1)->supports('float'));
        $this->assertTrue($callback1->argument(1)->supports('bool'));
        $this->assertTrue($callback1->argument(1)->supports(Object5::class));
        $this->assertFalse($callback1->argument(1)->supports('int', Argument::STRICT));
        $this->assertFalse($callback1->argument(1)->supports(Object5::class, Argument::STRICT));

        $this->assertTrue($callback1->argument(2)->supports('int'));
        $this->assertTrue($callback1->argument(2)->supports('integer'));
        $this->assertTrue($callback1->argument(2)->supports('float'));
        $this->assertFalse($callback1->argument(2)->supports('float', Argument::STRICT));
        $this->assertTrue($callback1->argument(2)->supports('bool'));
        $this->assertFalse($callback1->argument(2)->supports('bool', Argument::STRICT));
        $this->assertTrue($callback1->argument(2)->supports('string'));
        $this->assertFalse($callback1->argument(2)->supports('string', Argument::STRICT));

        $this->assertTrue($callback1->argument(3)->supports(Object1::class));
        $this->assertTrue($callback1->argument(3)->supports(Object2::class));
        $this->assertTrue($callback1->argument(3)->supports('string'));
        $this->assertTrue($callback1->argument(3)->supports('int'));

        $this->assertTrue($callback1->argument(4)->supports('float'));
        $this->assertTrue($callback1->argument(4)->supports('double'));
        $this->assertTrue($callback1->argument(4)->supports('int'));
        $this->assertTrue($callback1->argument(4)->supports('int', Argument::STRICT));
        $this->assertFalse($callback1->argument(4)->supports('int', Argument::VERY_STRICT));
        $this->assertTrue($callback1->argument(4)->supports('string'));
        $this->assertFalse($callback1->argument(4)->supports('string', Argument::STRICT));
        $this->assertTrue($callback1->argument(4)->supports('bool'));
        $this->assertFalse($callback1->argument(4)->supports('bool', Argument::STRICT));

        $this->assertTrue($callback1->argument(5)->supports('bool'));
        $this->assertTrue($callback1->argument(5)->supports('boolean'));
        $this->assertTrue($callback1->argument(5)->supports('float'));
        $this->assertFalse($callback1->argument(5)->supports('float', Argument::STRICT));
        $this->assertTrue($callback1->argument(5)->supports('int'));
        $this->assertFalse($callback1->argument(5)->supports('int', Argument::STRICT));
        $this->assertTrue($callback1->argument(5)->supports('string'));
        $this->assertFalse($callback1->argument(5)->supports('string', Argument::STRICT));

        $this->assertTrue($callback2->argument(0)->supports(Object1::class, Argument::COVARIANCE | Argument::CONTRAVARIANCE));
        $this->assertFalse($callback2->argument(0)->supports(Object3::class, Argument::COVARIANCE | Argument::CONTRAVARIANCE));
    }

    /**
     * @test
     */
    public function argument_allows(): void
    {
        $callback1 = Callback::createFor(function(Object1 $object, string $string, int $int, $noType, float $float) {});
        $callback2 = Callback::createFor(function(Object2 $object, string $string, $noType) {});

        $this->assertTrue($callback1->argument(0)->allows(new Object1()));
        $this->assertTrue($callback1->argument(0)->allows(new Object2()));
        $this->assertFalse($callback1->argument(0)->allows('string'));
        $this->assertFalse($callback1->argument(0)->allows(new Object3()));

        $this->assertTrue($callback1->argument(1)->allows('string'));
        $this->assertTrue($callback1->argument(1)->allows(16));
        $this->assertTrue($callback1->argument(1)->allows(16.7));
        $this->assertTrue($callback1->argument(1)->allows(true));
        $this->assertFalse($callback1->argument(1)->allows(16, true));

        $this->assertTrue($callback1->argument(2)->allows(16));
        $this->assertTrue($callback1->argument(2)->allows('17'));
        $this->assertTrue($callback1->argument(2)->allows(18.0));
        $this->assertFalse($callback1->argument(2)->allows('string'), 'non-numeric strings are not allowed');

        $this->assertTrue($callback1->argument(3)->allows(new Object1()));
        $this->assertTrue($callback1->argument(3)->allows(new Object2()));
        $this->assertTrue($callback1->argument(3)->allows('string'));
        $this->assertTrue($callback1->argument(3)->allows(16));

        $this->assertTrue($callback1->argument(4)->allows(16));
        $this->assertTrue($callback1->argument(4)->allows('17'));
        $this->assertTrue($callback1->argument(4)->allows('17.3'));
        $this->assertTrue($callback1->argument(4)->allows(18.0));
        $this->assertFalse($callback1->argument(4)->allows('string'), 'non-numeric strings are not allowed');

        $this->assertFalse($callback2->argument(0)->allows(new Object1()));
        $this->assertFalse($callback2->argument(0)->allows(new Object3()));
    }

    /**
     * @test
     */
    public function self_parameter_type(): void
    {
        $callback = Callback::createFor(Object6::closureSelf());

        $this->assertSame(Object6::class, $callback->argument(0)->type());

        $this->assertTrue($callback->argument(0)->supports(Object6::class));
        $this->assertTrue($callback->argument(0)->supports(Object7::class));
        $this->assertFalse($callback->argument(0)->supports(Object5::class));
        $this->assertFalse($callback->argument(0)->supports('int'));

        $this->assertTrue($callback->argument(0)->allows(new Object6()));
        $this->assertTrue($callback->argument(0)->allows(new Object7()));
        $this->assertFalse($callback->argument(0)->allows(new Object5()));
        $this->assertFalse($callback->argument(0)->allows(6));
    }

    /**
     * @test
     */
    public function invoke_all_union_parameter_with_defaults(): void
    {
        $callback = Callback::createFor(fn(string $a, ?\DateTimeInterface $b = null, $c = null) => [$a, $b, $c]);

        $ret = $callback->invokeAll(Parameter::union(
            Parameter::typed('string', 'a')
        ));

        $this->assertSame(['a', null, null], $ret);

        $ret = $callback->invokeAll(Parameter::union(
            Parameter::typed('string', 'a'),
            Parameter::typed(\DateTime::class, $b = new \DateTime())
        ));

        $this->assertSame(['a', $b, null], $ret);

        $ret = $callback->invokeAll(Parameter::union(
            Parameter::typed('string', 'a'),
            Parameter::untyped('c')
        ));

        $this->assertSame(['a', null, 'c'], $ret);

        $ret = $callback->invokeAll(Parameter::union(
            Parameter::untyped('c'),
            Parameter::typed('string', 'a'),
            Parameter::typed(\DateTime::class, $b = new \DateTime())
        ));

        $this->assertSame(['a', $b, 'c'], $ret);

        $ret = $callback->invokeAll(Parameter::union(
            Parameter::typed('string', 'a'),
            Parameter::typed(\DateTime::class, Parameter::factory(fn() => $b))
        ));

        $this->assertSame(['a', $b, null], $ret);
    }

    /**
     * @test
     */
    public function to_string_object(): void
    {
        $callback = Callback::createFor(function(Object1 $o, string $s) {});

        $this->assertFalse($callback->argument(0)->supports(Object5::class));
        $this->assertTrue($callback->argument(1)->supports(Object5::class));

        $this->assertFalse($callback->argument(0)->allows(new Object5()));
        $this->assertTrue($callback->argument(1)->allows(new Object5()));
    }
}

class Object1
{
}

class Object2 extends Object1
{
}

class Object3
{
}

class Object4
{
    public function __invoke()
    {
    }

    public static function staticMethod()
    {
    }
}

class Object5
{
    public function __toString(): string
    {
        return 'value';
    }
}

function test_function()
{
}

class Object6
{
    public static function closureSelf(): \Closure
    {
        return fn(self $object) => $object;
    }
}

class Object7 extends Object6
{
}
