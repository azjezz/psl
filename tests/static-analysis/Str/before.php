<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;
use Psl\Str;

/** @param lowercase-string $_foo */
function take_lowercase_string(string $_foo): void
{
}
/** @return lowercase-string */
function return_lowercase_string(): string
{
    return 'hello';
}

/**
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    $str = Str\before(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\before_last(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\before_last_ci(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Byte\before(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Byte\before_last(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Byte\before_last_ci(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\before(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\before_last(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\before_last_ci(return_lowercase_string(), 'h');
    Psl\invariant($str !== null, '!');
    take_lowercase_string($str);
}
