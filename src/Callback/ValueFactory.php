<?php

/*
 * This file is part of the zenstruck/callback package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Callback;

use Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ValueFactory
{
    /** @var callable */
    private $factory;

    /**
     * @param callable<string|array|Argument|null> $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(Argument $argument)
    {
        $stringTypeFactory = Parameter::factory(function() use ($argument) {
            if ($argument->isUnionType()) {
                throw new \LogicException(\sprintf('ValueFactory does not support union types. Inject "%s" instead.', Argument::class));
            }

            return $argument->type();
        });

        return Callback::createFor($this->factory)
            ->invoke(Parameter::union(
                Parameter::typed(Argument::class, $argument),
                Parameter::typed('array', $argument->types()),
                Parameter::typed('string', $stringTypeFactory),
                Parameter::untyped($stringTypeFactory)
            )->optional())
        ;
    }
}
