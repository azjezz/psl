<?php

declare(strict_types=1);

namespace Psl\Tree;

use Override;

/**
 * Immutable tree node implementation (node with children).
 *
 * @api
 */
final readonly class TreeNode<out T> implements NodeInterface<T>
{
    /**
     * @var list<NodeInterface<T>>
     */
    private array $children;

    /**
     * @param list<NodeInterface<T>> $children
     */
    public function __construct(
        private T $value,
        array $children = [],
    ) {
        $this->children = $children;
    }

    /**
     * @psalm-mutation-free
     */
    #[Override]
    public function getValue(): T
    {
        return $this->value;
    }

    /**
     * @return list<NodeInterface<T>>
     *
     * @psalm-mutation-free
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return array{
     *   value: T,
     *   children: list<array{
     *     value: T,
     *     children: list<array{
     *       value: T,
     *       children: list<array{
     *         value: T,
     *         children: list<array{
     *             value: T,
     *             children: list<array{
     *                 value: T,
     *                 children: list<array>
     *             }>
     *         }>
     *       }>
     *     }>
     *   }>
     * }
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return namespace\to_array::<T>($this);
    }
}
