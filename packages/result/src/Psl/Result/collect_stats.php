<?php

declare(strict_types=1);

namespace Psl\Result;

/**
 * @param iterable<array-key, ResultInterface<T>> $results
 *
 * @api
 */
function collect_stats<T = mixed>(iterable $results): Stats
{
    $stats = new Stats();
    foreach ($results as $result) {
        $stats = $stats->apply($result);
    }

    return $stats;
}
