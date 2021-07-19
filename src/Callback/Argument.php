<?php

namespace Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Argument
{
    public const EXACT = 2;
    public const COVARIANCE = 4;
    public const CONTRAVARIANCE = 8;

    /** @var \ReflectionNamedType[] */
    private $types = [];

    public function __construct(\ReflectionParameter $parameter)
    {
        if (!$type = $parameter->getType()) {
            return;
        }

        if ($type instanceof \ReflectionNamedType) {
            $this->types = [$type];

            return;
        }

        /** @var \ReflectionUnionType $type */
        $this->types = $type->getTypes();
    }

    public function type(): ?string
    {
        return $this->hasType() ? \implode('|', $this->types()) : null;
    }

    /**
     * @return string[]
     */
    public function types(): array
    {
        return \array_map(static function(\ReflectionNamedType $type) { return $type->getName(); }, $this->types);
    }

    public function hasType(): bool
    {
        return !empty($this->types);
    }

    public function isUnionType(): bool
    {
        return \count($this->types) > 1;
    }

    /**
     * @param string   $type    The type to check if this argument supports
     * @param int|null $options {@see EXACT} to only check if exact match
     *                          {@see COVARIANCE} to check if exact or, if class, is instanceof argument type
     *                          {@see CONTRAVARIANCE} to check if exact or, if class, argument type is instance of class
     *                          Bitwise disjunction of above is allowed
     */
    public function supports(string $type, int $options = self::EXACT|self::COVARIANCE): bool
    {
        if (!$this->hasType()) {
            // no type-hint so any type is supported
            return true;
        }

        foreach ($this->types() as $supportedType) {
            if ($options & self::EXACT && $supportedType === $type) {
                return true;
            }

            if ($options & self::COVARIANCE && \is_a($type, $supportedType, true)) {
                return true;
            }

            if ($options & self::CONTRAVARIANCE && \is_a($supportedType, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
