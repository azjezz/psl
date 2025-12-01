<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Result;

use Psl\Option;
use Psl\Result;

/**
 * @param Result\ResultInterface<42> $data
 *
 * @return Option\Option<42>
 */
function test_to_option(Result\ResultInterface $data): Option\Option
{
    return $data->toOption();
}
