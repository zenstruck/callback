<?php

namespace Zenstruck\Callback\Parameter;

use Zenstruck\Callback\Argument;
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

    protected function valueFor(Argument $argument)
    {
        if (!$argument->hasType()) {
            throw new UnresolveableArgument('Argument has no type.');
        }

        if ($argument->supports($this->type, Argument::COVARIANCE|Argument::CONTRAVARIANCE|Argument::VERY_STRICT)) {
            return $this->value;
        }

        throw new UnresolveableArgument('Unable to resolve.');
    }
}
