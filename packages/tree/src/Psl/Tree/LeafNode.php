<?php

declare(strict_types=1);

namespace Psl\Tree;

use Override;

/**
 * Immutable leaf node implementation (node with no children).
 *
 * @api
 */
final readonly class LeafNode<out T> implements NodeInterface<T>
{
    public function __construct(
        private T $value,
    ) {}

    /**
     * @psalm-mutation-free
     */
    #[Override]
    public function getValue(): T
    {
        return $this->value;
    }

    /**
     * @return array{value: T, ...}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return namespace\to_array::<T>($this);
    }
}
