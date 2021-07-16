<?php

namespace Zenstruck;

use Zenstruck\Callback\Argument;
use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Callback implements \Countable
{
    /** @var \ReflectionFunction */
    private $function;

    private function __construct(\ReflectionFunction $function)
    {
        $this->function = $function;
    }

    public function __toString(): string
    {
        if ($class = $this->function->getClosureScopeClass()) {
            return "{$class->getName()}:{$this->function->getStartLine()}";
        }

        return $this->function->getName();
    }

    /**
     * @param callable|\ReflectionFunction $value
     */
    public static function createFor($value): self
    {
        if (\is_callable($value)) {
            $value = new \ReflectionFunction(\Closure::fromCallable($value));
        }

        if (!$value instanceof \ReflectionFunction) {
            throw new \InvalidArgumentException('$value must be callable.');
        }

        return new self($value);
    }

    /**
     * Invoke the callable with the passed arguments. Arguments of type
     * Zenstruck\Callback\Parameter are resolved before invoking.
     *
     * @param mixed|Parameter ...$arguments
     *
     * @return mixed
     *
     * @throws \ArgumentCountError   If there is a argument count mismatch
     * @throws UnresolveableArgument If the argument cannot be resolved
     */
    public function invoke(...$arguments)
    {
        $functionArgs = $this->arguments();

        foreach ($arguments as $key => $parameter) {
            if (!$parameter instanceof Parameter) {
                continue;
            }

            if (!\array_key_exists($key, $functionArgs)) {
                if (!$parameter->isOptional()) {
                    throw new \ArgumentCountError(\sprintf('No argument %d for callable. Expected type: "%s".', $key + 1, $parameter->type()));
                }

                $arguments[$key] = null;

                continue;
            }

            try {
                $arguments[$key] = $parameter->resolve($functionArgs[$key]);
            } catch (UnresolveableArgument $e) {
                throw new UnresolveableArgument(\sprintf('Unable to resolve argument %d for callback. Expected type: "%s". (%s)', $key + 1, $parameter->type(), $this), $e);
            }
        }

        return $this->function->invoke(...$arguments);
    }

    /**
     * Invoke the callable using the passed Parameter to resolve all callable
     * arguments.
     *
     * @param int $min Enforce a minimum number of arguments the callable must have
     *
     * @return mixed
     *
     * @throws \ArgumentCountError   If the number of arguments is less than $min
     * @throws UnresolveableArgument If the argument cannot be resolved
     */
    public function invokeAll(Parameter $parameter, int $min = 0)
    {
        if (\count($this) < $min) {
            throw new \ArgumentCountError("{$min} argument(s) of type \"{$parameter->type()}\" required ({$this}).");
        }

        $arguments = $this->arguments();

        foreach ($arguments as $key => $argument) {
            try {
                $arguments[$key] = $parameter->resolve($argument);
            } catch (UnresolveableArgument $e) {
                throw new UnresolveableArgument(\sprintf('Unable to resolve argument %d for callback. Expected type: "%s". (%s)', $key + 1, $parameter->type(), $this), $e);
            }
        }

        return $this->function->invoke(...$arguments);
    }

    /**
     * @return Argument[]
     */
    public function arguments(): array
    {
        return \array_map(
            static function(\ReflectionParameter $parameter) {
                return new Argument($parameter);
            },
            $this->function->getParameters()
        );
    }

    public function argument(int $index): Argument
    {
        if (!isset(($arguments = $this->arguments())[$index])) {
            throw new \OutOfRangeException(\sprintf('Argument %d does not exist for %s.', $index + 1, $this));
        }

        return $arguments[$index];
    }

    public function count(): int
    {
        return $this->function->getNumberOfParameters();
    }
}
