<?php

namespace Zenstruck\Callback\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Callback;
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
        $callback = Callback::createFor(function() { return 'ret'; });

        $this->expectException(\ArgumentCountError::class);

        $callback->invokeAll(Parameter::untyped('foo'), 1);
    }

    /**
     * @test
     */
    public function invoke_all_with_no_arguments(): void
    {
        $actual = Callback::createFor(function() { return 'ret'; })
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
        $actual = Callback::createFor(function($string) { return \mb_strtoupper($string); })
            ->invokeAll(Parameter::untyped('foobar'))
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_primitive_typed_argument(): void
    {
        $actual = Callback::createFor(function(string $string) { return \mb_strtoupper($string); })
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
        $this->expectExceptionMessage('Unable to resolve argument 3 for callback. Expected type: "mixed|Zenstruck\Callback\Tests\Object1"');

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
        $actual = Callback::createFor(function() { return 'ret'; })
            ->invoke()
        ;

        $this->assertSame('ret', $actual);
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
