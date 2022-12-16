<?php

/*
 * This file is part of the zenstruck/callback package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Callback\Parameter;

use Zenstruck\Callback\Argument;
use Zenstruck\Callback\Exception\UnresolveableArgument;
use Zenstruck\Callback\Parameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UnionParameter extends Parameter
{
    /** @var Parameter[] */
    private $parameters;

    public function __construct(Parameter ...$parameters)
    {
        if (empty($parameters)) {
            throw new \InvalidArgumentException('At least one argument is required.');
        }

        $this->parameters = $parameters;
    }

    public function type(): string
    {
        return \implode('|', \array_map(static fn(Parameter $param) => $param->type(), $this->parameters));
    }

    protected function valueFor(Argument $argument)
    {
        foreach ($this->parameters as $parameter) {
            try {
                return $parameter->valueFor($argument);
            } catch (UnresolveableArgument $e) {
                continue;
            }
        }

        throw new UnresolveableArgument('Unable to resolve.');
    }
}
