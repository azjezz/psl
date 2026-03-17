<?php

declare(strict_types=1);

namespace Psl\Tree\Exception;

use Psl\Exception\InvalidArgumentException as PslInvalidArgumentException;

final class NoRootNodeException extends PslInvalidArgumentException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('No root item found. At least one item must have parent_id = null.');
    }
}
