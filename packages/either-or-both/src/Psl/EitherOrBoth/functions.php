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
 * @pure
 *
 * @api
 */
function left<TLeft>(TLeft $value): Left<TLeft>
{
    return new Left::<TLeft>($value);
}

/**
 * Create a {@see Right} variant of {@see EitherOrBoth}.
 *
 * @pure
 *
 * @api
 */
function right<TRight>(TRight $value): Right<TRight>
{
    return new Right::<TRight>($value);
}

/**
 * Create a {@see Both} variant of {@see EitherOrBoth}.
 *
 * @pure
 *
 * @api
 */
function both<TLeft, TRight>(TLeft $left, TRight $right): Both<TLeft, TRight>
{
    return new Both::<TLeft, TRight>($left, $right);
}
