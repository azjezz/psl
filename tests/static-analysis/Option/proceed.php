<?php

declare(strict_types=1);

use Psl\Option;

function proceed(): void
{
    /**
     * @param Option\Option<int> $option
     *
     * @return non-empty-string
     */
    function test_proceed(Option\Option $option): string
    {
        return $option->proceed(static fn(int $value) => "There is $value of them.", static fn() => 'There are none.');
    }
}
