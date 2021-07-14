<?php

namespace Zenstruck\Callback;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Argument
{
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

    public function supports(string $type): bool
    {
        if (!$this->hasType()) {
            // no type-hint so any type is supported
            return true;
        }

        foreach ($this->types() as $t) {
            if ($t === $type || \is_a($t, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
