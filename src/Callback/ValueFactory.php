<?php

namespace Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ValueFactory
{
    /** @var callable */
    private $factory;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(?string $type)
    {
        return ($this->factory)($type);
    }
}
