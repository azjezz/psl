<?php

declare(strict_types=1);

namespace Psl\Tree;

use Override;

/**
 * Immutable tree node implementation (node with children).
 *
 * @template T
 *
 * @implements NodeInterface<T>
 */
final readonly class TreeNode implements NodeInterface
{
    /**
     * @var list<NodeInterface<T>>
     */
    private array $children;

    /**
     * @param T $value
     * @param list<NodeInterface<T>> $children
     */
    public function __construct(
        private mixed $value,
        array $children = [],
    ) {
        $this->children = $children;
    }

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
        return namespace\to_array($this);
    }
}
