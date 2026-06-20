<?php

declare(strict_types=1);

namespace Psl\Option\Tests\StaticAnalysis;

use Psl\Option;

function proceed(): void
{
    /**
     * @param Option\Option<int> $option
     *
     * @return non-empty-string
     */
    function test_proceed(Option\Option<int> $option): string
    {
        return $option->proceed::<string>(
            static fn(int $value): string => "There is {$value} of them.",
            static fn(): string => 'There are none.',
        );
    }
}
