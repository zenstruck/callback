<?php

namespace Zenstruck;

use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Callback
{
    /** @var \ReflectionFunction */
    private $function;

    private function __construct(\ReflectionFunction $function)
    {
        $this->function = $function;
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
     * @param mixed ...$arguments
     *
     * @return mixed
     *
     * @throws UnresolveableArgument
     */
    public function invoke(...$arguments)
    {
        $parameters = $this->function->getParameters();

        foreach ($arguments as $key => $argument) {
            if (!$argument instanceof Parameter) {
                continue;
            }

            if (!\array_key_exists($key, $parameters) && !$argument->isOptional()) {
                throw new \ArgumentCountError(\sprintf('No argument %d for callable. Expected type: "%s".', $key + 1, $argument->type()));
            }

            if ($argument->isOptional()) {
                $arguments[$key] = null;

                continue;
            }

            try {
                $arguments[$key] = $argument->resolve($parameters[$key]);
            } catch (UnresolveableArgument $e) {
                throw new UnresolveableArgument(\sprintf('Unable to resolve argument %d for callback. Expected type: "%s".', $key + 1, $argument->type()), $e);
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
     * @throws UnresolveableArgument
     */
    public function invokeAll(Parameter $parameter, int $min = 0)
    {
        $arguments = $this->function->getParameters();

        if (\count($arguments) < $min) {
            throw new \ArgumentCountError("{$min} argument(s) of type \"{$parameter->type()}\" required.");
        }

        foreach ($arguments as $key => $argument) {
            try {
                $arguments[$key] = $parameter->resolve($argument);
            } catch (UnresolveableArgument $e) {
                throw new UnresolveableArgument(\sprintf('Unable to resolve argument %d for callback. Expected type: "%s"', $key + 1, $parameter->type()), $e);
            }
        }

        return $this->function->invoke(...$arguments);
    }
}
