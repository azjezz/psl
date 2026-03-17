<?php

declare(strict_types=1);

namespace Psl\Tree;

use Override;

/**
 * Immutable leaf node implementation (node with no children).
 *
 * @template T
 *
 * @implements NodeInterface<T>
 */
final readonly class LeafNode implements NodeInterface
{
    /**
     * @param T $value
     */
    public function __construct(
        private mixed $value,
    ) {}

    /**
     * @return T
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return array{value: T, ...}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return namespace\to_array($this);
    }
}
