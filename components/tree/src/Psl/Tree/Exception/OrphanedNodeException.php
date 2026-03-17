<?php

declare(strict_types=1);

namespace Psl\Tree\Exception;

use Psl\Exception\InvalidArgumentException as PslInvalidArgumentException;

final class OrphanedNodeException extends PslInvalidArgumentException implements ExceptionInterface
{
    /**
     * @param mixed $nodeId
     * @param mixed $parentId
     */
    public function __construct(mixed $nodeId, mixed $parentId)
    {
        parent::__construct("Node with id '{$nodeId}' references non-existent parent_id '{$parentId}'.");
    }
}
