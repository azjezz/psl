<?php

declare(strict_types=1);

/**
 * Free-function constructors for {@see \Psl\EitherOrBoth\EitherOrBoth} variants.
 *
 * Bundled into a single file instead of the usual one-function-per-file convention
 * (see packages/option/src/Psl/Option/some.php etc.) because the variant classes
 * {@see \Psl\EitherOrBoth\Left}, {@see \Psl\EitherOrBoth\Right}, and
 * {@see \Psl\EitherOrBoth\Both} already occupy the PascalCase files in this
 * directory. On a case-insensitive filesystem (default macOS APFS, Windows NTFS),
 * `left.php` and `Left.php` resolve to the same path, so separate files would
 * collide with the class files. Consolidating here avoids the collision while
 * keeping the functions discoverable under the `Psl\EitherOrBoth\` namespace.
 */

namespace Psl\EitherOrBoth;

/**
 * Create a {@see Left} variant of {@see EitherOrBoth}.
 *
 * @template TLeft
 *
 * @param TLeft $value
 *
 * @return Left<TLeft>
 *
 * @pure
 *
 * @api
 */
function left(mixed $value): Left
{
    return new Left($value);
}

/**
 * Create a {@see Right} variant of {@see EitherOrBoth}.
 *
 * @template TRight
 *
 * @param TRight $value
 *
 * @return Right<TRight>
 *
 * @pure
 *
 * @api
 */
function right(mixed $value): Right
{
    return new Right($value);
}

/**
 * Create a {@see Both} variant of {@see EitherOrBoth}.
 *
 * @template TLeft
 * @template TRight
 *
 * @param TLeft  $left
 * @param TRight $right
 *
 * @return Both<TLeft, TRight>
 *
 * @pure
 *
 * @api
 */
function both(mixed $left, mixed $right): Both
{
    return new Both($left, $right);
}
