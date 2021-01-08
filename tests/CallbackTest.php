<?php

namespace Zenstruck\Callback\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackTest extends TestCase
{
    /**
     * @test
     */
    public function can_execute_string_callbacks(): void
    {
        $actual = Callback::createFor('strtoupper')
            ->minArguments(1)
            ->replaceUntypedArgument('foobar')
            ->execute()
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function can_execute_closures(): void
    {
        $actual = Callback::createFor(function($string) { return \mb_strtoupper($string); })
            ->minArguments(1)
            ->replaceUntypedArgument('foobar')
            ->execute()
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function can_enforce_min_arguments(): void
    {
        $callback = Callback::createFor(function() { return 'ret'; })
            ->minArguments(1)
        ;

        $this->expectException(\ArgumentCountError::class);

        $callback->execute();
    }

    /**
     * @test
     */
    public function can_replace_primitive_typehints(): void
    {
        $actual = Callback::createFor(function(string $string) { return \mb_strtoupper($string); })
            ->minArguments(1)
            ->replaceTypedArgument('string', 'foobar')
            ->execute()
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function can_replace_class_argument(): void
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
            ->replaceTypedArgument(Object1::class, $object)
            ->replaceUntypedArgument($object)
            ->execute()
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
    public function can_replace_class_typehint_with_factory(): void
    {
        $function = static function(Object1 $object1, Object2 $object2, $object3) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
            ];
        };
        $factoryArgs = [];
        $factory = static function($arg = null) use (&$factoryArgs) {
            $factoryArgs[] = $arg;

            return new Object2();
        };

        $ret = Callback::createFor($function)
            ->replaceTypedArgument(Object1::class, $factory)
            ->replaceUntypedArgument($factory)
            ->execute()
        ;

        $this->assertSame(['object1', 'object2', 'object3'], \array_keys($ret));
        $this->assertInstanceOf(Object2::class, $ret['object1']);
        $this->assertInstanceOf(Object2::class, $ret['object2']);
        $this->assertInstanceOf(Object2::class, $ret['object3']);
        $this->assertSame(
            [Object1::class, Object2::class, null],
            $factoryArgs
        );
    }

    /**
     * @test
     */
    public function type_error_thrown_if_no_untyped_argument_defined(): void
    {
        $callback = Callback::createFor(static function($arg) {});

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('No replaceUntypedArgument set');

        $callback->execute();
    }

    /**
     * @test
     */
    public function type_error_thrown_if_type_argument_not_defined(): void
    {
        $callback = Callback::createFor(static function(Object2 $object1) {})
            ->replaceTypedArgument(\stdClass::class, new \stdClass())
        ;

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Unable to replace argument "object1"');

        $callback->execute();
    }

    /**
     * @test
     */
    public function create_must_be_callable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Callback::createFor('not a callable');
    }
}

class Object1
{
}

class Object2 extends Object1
{
}
