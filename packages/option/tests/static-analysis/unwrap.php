<?php

declare(strict_types=1);

namespace Psl\Option\Tests\StaticAnalysis;

use Psl\Option;

function test_some_unwrap_or(): null|string
{
    return Option\some::<string>('string')->unwrapOr::<null>(null);
}

function test_none_unwrap_or(): null|string
{
    return Option\none()->unwrapOr::<null>(null);
}

function test_some_unwrap_or_else(): null|string
{
    return Option\some::<string>('string')->unwrapOrElse::<null>(static fn(): null => null);
}

function test_none_unwrap_or_else(): null|string
{
    return Option\none()->unwrapOrElse::<null>(static fn(): null => null);
}
