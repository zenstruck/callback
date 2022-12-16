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
use Zenstruck\Callback\ValueFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UntypedParameter extends Parameter
{
    private $value;

    /**
     * @param mixed|ValueFactory $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function type(): string
    {
        return 'mixed';
    }

    protected function valueFor(Argument $argument)
    {
        if ($argument->hasType()) {
            throw new UnresolveableArgument('Argument has type.');
        }

        return $this->value;
    }
}
