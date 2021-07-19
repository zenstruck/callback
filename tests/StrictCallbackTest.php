<?php

declare(strict_types=1);

namespace Zenstruck\Callback\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Callback;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class StrictCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function invoke_with_non_parameters(): void
    {
        $callback = Callback::createFor(
            function(string $string, float $float, ?int $int = null) { return [$string, $float, $int]; }
        );

        $this->assertSame(['6.2', 3.0, null], $callback->invoke(6.2, '3'));
    }

    /**
     * @test
     */
    public function invoke_with_parameter(): void
    {
        $ret = Callback::createFor(
            function(string $string, float $float, ?int $int = null) { return [$string, $float, $int]; }
        )->invoke(
            Parameter::typed('string', 6.2),
            Parameter::union(
                Parameter::typed('float', 3),
                Parameter::typed('string', '6.2')
            )
        );

        $this->assertSame(['6.2', 3.0, null], $ret);
    }

    /**
     * @test
     */
    public function invoke_all(): void
    {
        $ret = Callback::createFor(
            function(string $string, float $float, int $int = 16) { return [$string, $float, $int]; }
        )->invokeAll(
            Parameter::union(
                Parameter::typed('float', 3),
                Parameter::typed('string', '6.2')
            )
        );

        $this->assertSame(['6.2', 3.0, 16], $ret);
    }
}
