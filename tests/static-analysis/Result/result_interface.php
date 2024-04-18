<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Iter;

use Exception;
use Psl\Result\Failure;
use Psl\Result\ResultInterface;
use Psl\Result\Success;

/**
 * @param ResultInterface<int> $result
 *
 * @throws Exception
 *
 * @return Failure<int, Exception>
 */
function return_failure(ResultInterface $result): Failure
{
    if ($result->isFailed()) {
        return $result;
    }

    throw new Exception();
}

/**
 * @param ResultInterface<int> $result
 *
 * @throws Exception
 *
 * @return Success<int>
 */
function return_success(ResultInterface $result): Success
{
    if ($result->isSucceeded()) {
        return $result;
    }

    throw new Exception();
}
