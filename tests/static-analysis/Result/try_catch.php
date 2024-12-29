<?php

declare(strict_types=1);

use Psl\Result;

function test_try_catch(): null|string
{
    return Result\try_catch(static fn(): string => 'hello', static fn(): null|string => null);
}

function test_try_catch_composed(): null|string
{
    return (static fn(int $id) => Result\try_catch(
        static fn(): string => 'hello ' . ((string) $id),
        static fn(): null|string => null,
    ))(1);
}
