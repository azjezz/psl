<?php

declare(strict_types=1);

namespace Psl\Dict;

use function is_array;
use function array_unique;

/**
 * Returns a new dict in which each value appears exactly once.
 *
 * @psalm-param iterable<array-key, scalar> $iterable
 *
 * @psalm-return array<scalar>
 */
function unique_scalar(iterable $iterable): array
{

    if (is_array($iterable)) {
        return array_unique($iterable);
    }

    return unique_by(
        $iterable,
        /**
         * @psalm-param     scalar  $v
         *
         * @psalm-return    scalar
         *
         * @psalm-pure
         */
        static fn($v) => $v
    );
}
