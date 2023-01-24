<?php

declare(strict_types=1);

use Psl\Option;

function test_some_unwrap_or(): ?string
{
    return Option\some('string')->unwrapOr(null);
}

function test_none_unwrap_or(): ?string
{
    return Option\none()->unwrapOr(null);
}

function test_some_unwrap_or_else(): ?string
{
    return Option\some('string')->unwrapOrElse(static fn () => null);
}

function test_none_unwrap_or_else(): ?string
{
    return Option\none()->unwrapOrElse(static fn() => null);
}
