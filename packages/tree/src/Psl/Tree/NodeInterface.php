<?php

declare(strict_types=1);

namespace Psl\Tree;

use JsonSerializable;

/**
 * Base interface for all tree nodes.
 *
 * @psalm-inheritors LeafNode|TreeNode
 *
 * @api
 */
interface NodeInterface<out T> extends JsonSerializable
{
    /**
     * Returns the value stored in this node.
     *
     * @psalm-mutation-free
     */
    public function getValue(): T;
}
