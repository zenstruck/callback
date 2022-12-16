<?php

declare(strict_types=1);

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
            fn(string $string, float $float, ?int $int = null) => [$string, $float, $int]
        );

        $this->assertSame(['6.2', 3.0, null], $callback->invoke(6.2, '3'));
    }

    /**
     * @test
     */
    public function invoke_with_parameter(): void
    {
        $ret = Callback::createFor(
            fn(string $string, float $float, ?int $int = null) => [$string, $float, $int]
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
            fn(string $string, float $float, int $int = 16) => [$string, $float, $int]
        )->invokeAll(
            Parameter::union(
                Parameter::typed('float', 3),
                Parameter::typed('string', '6.2')
            )
        );

        $this->assertSame(['6.2', 3.0, 16], $ret);
    }
}
