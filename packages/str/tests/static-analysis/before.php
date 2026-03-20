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
    $str = Str\before(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\before_last(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\before_last_ci(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Byte\before(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Byte\before_last(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Byte\before_last_ci(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Grapheme\before(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Grapheme\before_last(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);

    $str = Str\Grapheme\before_last_ci(namespace\return_lowercase_string(), 'h');
    Psl\invariant(null !== $str, '!');
    namespace\take_lowercase_string($str);
}
