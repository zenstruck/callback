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
    public function create_must_be_callable(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Callback::createFor('not a callable');
    }
}
