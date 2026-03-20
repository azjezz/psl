<?php

declare(strict_types=1);

namespace Psl\Str\Tests\StaticAnalysis;

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
    $str = Str\after(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\after_last(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\after_last_ci(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Byte\after(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Byte\after_last(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Byte\after_last_ci(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Grapheme\after(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Grapheme\after_last(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Grapheme\after_last_ci(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);
}
