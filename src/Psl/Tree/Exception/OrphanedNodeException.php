<?php

declare(strict_types=1);

namespace Psl\Tree\Exception;

use Psl\Exception\InvalidArgumentException as PslInvalidArgumentException;

final class OrphanedNodeException extends PslInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $node_id
     * @param mixed $parent_id
     */
    public function __construct(mixed $node_id, mixed $parent_id)
    {
        parent::__construct("Node with id '{$node_id}' references non-existent parent_id '{$parent_id}'.");
    }
}
