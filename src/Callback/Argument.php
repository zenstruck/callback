<?php

namespace Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Argument
{
    /**
     * Allow type to match exactly {@see supports()}.
     */
    public const EXACT = 2;

    /**
     * If type is class, parent classes are supported {@see supports()}.
     */
    public const COVARIANCE = 4;

    /**
     * If type is class, child classes are supported {@see supports()}.
     */
    public const CONTRAVARIANCE = 8;

    private const TYPE_NORMALIZE_MAP = [
        'boolean' => 'bool',
        'integer' => 'int',
        'resource (closed)' => 'resource',
    ];

    /** @var \ReflectionParameter */
    private $parameter;

    /** @var \ReflectionNamedType[] */
    private $types = [];

    public function __construct(\ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
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
        return \array_map(static function(\ReflectionNamedType $type) { return $type->getName(); }, $this->reflectionTypes());
    }

    public function hasType(): bool
    {
        return !empty($this->types());
    }

    public function isUnionType(): bool
    {
        return \count($this->types()) > 1;
    }

    /**
     * @param string $type    The type to check if this argument supports
     * @param int    $options {@see EXACT}, {@see COVARIANCE}, {@see CONTRAVARIANCE}
     *                        Bitwise disjunction of above is allowed
     */
    public function supports(string $type, int $options = self::EXACT|self::COVARIANCE): bool
    {
        if (!$this->hasType()) {
            // no type-hint so any type is supported
            return true;
        }

        if ('null' === \mb_strtolower($type) && $this->parameter->allowsNull()) {
            return true;
        }

        $type = self::TYPE_NORMALIZE_MAP[$type] ?? $type;

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

    /**
     * @param mixed $value
     */
    public function allows($value): bool
    {
        return $this->supports(\is_object($value) ? \get_class($value) : \gettype($value));
    }

    /**
     * @return \ReflectionNamedType[]
     */
    private function reflectionTypes(): array
    {
        if (!$type = $this->parameter->getType()) {
            return [];
        }

        if ($type instanceof \ReflectionNamedType) {
            return [$type];
        }

        /** @var \ReflectionUnionType $type */
        return $type->getTypes();
    }
}
