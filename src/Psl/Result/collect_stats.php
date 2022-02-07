<?php

declare(strict_types=1);

namespace Psl\Result;

use function Psl\Iter\reduce;

/**
 * @template T
 *
 * @param iterable<array-key, ResultInterface<T>> $results
 */
function collect_stats(iterable $results): Stats
{
    return reduce(
        $results,
        static fn (Stats $stats, ResultInterface $result): Stats => $stats->apply($result),
        new Stats()
    );
}
