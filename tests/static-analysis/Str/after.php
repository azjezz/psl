<?php

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Str;

use Psl;
use Psl\Str;

/** @param lowercase-string $_ */
function take_lowercase_string(string $_): void {}

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
    $str = Str\after(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\after_last(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\after_last_ci(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\Byte\after(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\Byte\after_last(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\Byte\after_last_ci(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\after(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\after_last(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);

    $str = Str\Grapheme\after_last_ci(return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    take_lowercase_string($str);
}
