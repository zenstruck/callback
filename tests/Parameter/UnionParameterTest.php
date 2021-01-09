<?php

namespace Zenstruck\Callback\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Zenstruck\Callback\Parameter\UnionParameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UnionParameterTest extends TestCase
{
    /**
     * @test
     */
    public function must_have_at_least_one_parameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UnionParameter();
    }
}
