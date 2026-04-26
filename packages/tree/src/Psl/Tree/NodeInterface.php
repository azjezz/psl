<?php

declare(strict_types=1);

namespace Psl\Tree;

use JsonSerializable;

/**
 * Base interface for all tree nodes.
 *
 * @template-covariant T
 *
 * @psalm-inheritors LeafNode|TreeNode
 *
 * @api
 */
interface NodeInterface extends JsonSerializable
{
    /**
     * Returns the value stored in this node.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getValue(): mixed;
}
