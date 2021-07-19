<?php

namespace Zenstruck\Callback;

use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter\TypedParameter;
use Zenstruck\Callback\Parameter\UnionParameter;
use Zenstruck\Callback\Parameter\UntypedParameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Parameter
{
    /** @var bool */
    private $optional = false;

    final public static function union(self ...$parameters): self
    {
        return new UnionParameter(...$parameters);
    }

    final public static function typed(string $type, $value): self
    {
        return new TypedParameter($type, $value);
    }

    final public static function untyped($value): self
    {
        return new UntypedParameter($value);
    }

    final public static function factory(callable $factory): ValueFactory
    {
        return new ValueFactory($factory);
    }

    final public function optional(): self
    {
        $this->optional = true;

        return $this;
    }

    /**
     * @internal
     *
     * @return mixed
     *
     * @throws UnresolveableArgument
     */
    final public function resolve(Argument $argument)
    {
        $value = $this->valueFor($argument);

        if ($value instanceof ValueFactory) {
            $value = $value($argument);
        }

        if (!$argument->allows($value)) {
            throw new UnresolveableArgument(\sprintf('Unable to resolve argument. Expected "%s", got "%s".', $argument->type(), get_debug_type($value)));
        }

        return $value;
    }

    /**
     * @internal
     */
    final public function isOptional(): bool
    {
        return $this->optional;
    }

    abstract public function type(): string;

    abstract protected function valueFor(Argument $argument);
}
