<?php

declare(strict_types=1);

namespace Psl\Result;

/**
 * @template T
 *
 * @param iterable<array-key, ResultInterface<T>> $results
 *
 * @api
 */
function collect_stats(iterable $results): Stats
{
    $stats = new Stats();
    foreach ($results as $result) {
        $stats = $stats->apply($result);
    }

    return $stats;
}
