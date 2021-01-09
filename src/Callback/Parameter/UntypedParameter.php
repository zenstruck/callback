<?php

namespace Zenstruck\Callback\Parameter;

use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UntypedParameter extends Parameter
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function type(): string
    {
        return 'mixed';
    }

    protected function valueFor(\ReflectionParameter $parameter)
    {
        if ($parameter->getType()) {
            throw new UnresolveableArgument('Argument has type.');
        }

        return $this->value;
    }
}
