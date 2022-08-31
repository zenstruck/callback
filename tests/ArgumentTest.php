<?php

namespace Zenstruck\Callback\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArgumentTest extends TestCase
{
    /**
     * @test
     * @requires PHP >= 8.0
     */
    public function union_type(): void
    {
        eval('$callback = fn(int|string $arg) => null;');
        $arg = Callback::createFor($callback)->argument(0);

        $this->assertSame('string|int', $arg->type());
        $this->assertSame('string|int', (string) $arg);
        $this->assertTrue($arg->hasType());
        $this->assertFalse($arg->isNamedType());
        $this->assertTrue($arg->isUnionType());
        $this->assertFalse($arg->isIntersectionType());
    }

    /**
     * @test
     */
    public function named_type(): void
    {
        $arg = Callback::createFor(function(string $foo) {})->argument(0);

        $this->assertSame('string', $arg->type());
        $this->assertSame('string', (string) $arg);
        $this->assertTrue($arg->hasType());
        $this->assertTrue($arg->isNamedType());
        $this->assertFalse($arg->isUnionType());
        $this->assertFalse($arg->isIntersectionType());
    }

    /**
     * @test
     */
    public function no_type(): void
    {
        $arg = Callback::createFor(function($foo) {})->argument(0);

        $this->assertNull($arg->type());
        $this->assertSame('', (string) $arg);
        $this->assertFalse($arg->hasType());
        $this->assertFalse($arg->isNamedType());
        $this->assertFalse($arg->isUnionType());
        $this->assertFalse($arg->isIntersectionType());
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function intersection_type(): void
    {
        eval('$callback = fn(\Countable&\Iterator $arg) => null;');
        $arg = Callback::createFor($callback)->argument(0);

        $this->assertSame('Countable&Iterator', $arg->type());
        $this->assertSame('Countable&Iterator', (string) $arg);
        $this->assertTrue($arg->hasType());
        $this->assertFalse($arg->isNamedType());
        $this->assertFalse($arg->isUnionType());
        $this->assertTrue($arg->isIntersectionType());
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function supports_intersection(): void
    {
        eval('$callback = fn(\Countable&\IteratorAggregate $arg) => null;');
        $arg = Callback::createFor($callback)->argument(0);

        $this->assertFalse($arg->supports('string'));
        $this->assertFalse($arg->supports(\get_class(new class() implements \Countable {
            public function count(): int
            {
                return 0;
            }
        })));
        $this->assertTrue($arg->supports(\get_class(new class() implements \Countable, \IteratorAggregate {
            public function count(): int
            {
                return 0;
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator();
            }
        })));
    }
}
