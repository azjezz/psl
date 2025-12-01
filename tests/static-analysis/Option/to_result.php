<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Result;

use Psl\Option;
use Psl\Result;

/**
 * @param Option\Option<42> $data
 *
 * @return Result\ResultInterface<41>
 */
function test_to_option(Option\Option $data): Result\ResultInterface
{
    return $data->toResult();
}
