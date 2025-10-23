<?php

declare(strict_types=1);

namespace Psl\Tree\Exception;

use Psl\Exception\InvalidArgumentException as PslInvalidArgumentException;

final class MultipleRootNodesException extends PslInvalidArgumentException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Multiple root items found. Only one item should have parent_id = null.');
    }
}
