<?php

declare(strict_types=1);

use Psl\Result;

function test_try_catch(): ?string
{
    return Result\try_catch(
        static fn(): string => 'hello',
        static fn(): ?string => null,
    );
}


function test_try_catch_composed(): ?string
{
    return (
        static fn (int $id) => Result\try_catch(
            static fn(): string => 'hello ' . (string) $id,
            static fn(): ?string => null,
        )
    )(1);
}
