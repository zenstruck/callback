<?php

namespace Zenstruck\Callback\Parameter;

use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TypedParameter extends Parameter
{
    /** @var string */
    private $type;
    private $value;

    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function type(): string
    {
        return $this->type;
    }

    protected function valueFor(\ReflectionParameter $parameter)
    {
        $parameterType = $parameter->getType();

        if (!$parameterType instanceof \ReflectionNamedType) {
            throw new UnresolveableArgument('Argument has no type.');
        }

        if ($this->type === $parameterType->getName() || \is_a($parameterType->getName(), $this->type, true)) {
            return $this->value;
        }

        throw new UnresolveableArgument('Unable to resolve.');
    }
}
