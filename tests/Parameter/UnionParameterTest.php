<?php

/*
 * This file is part of the zenstruck/callback package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
