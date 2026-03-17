<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Dict\associate' => __DIR__ . '/Dict/associate.php',
        'Psl\Dict\count_values' => __DIR__ . '/Dict/count_values.php',
        'Psl\Dict\diff' => __DIR__ . '/Dict/diff.php',
        'Psl\Dict\diff_by_key' => __DIR__ . '/Dict/diff_by_key.php',
        'Psl\Dict\drop' => __DIR__ . '/Dict/drop.php',
        'Psl\Dict\drop_while' => __DIR__ . '/Dict/drop_while.php',
        'Psl\Dict\equal' => __DIR__ . '/Dict/equal.php',
        'Psl\Dict\filter' => __DIR__ . '/Dict/filter.php',
        'Psl\Dict\filter_keys' => __DIR__ . '/Dict/filter_keys.php',
        'Psl\Dict\filter_nonnull_by' => __DIR__ . '/Dict/filter_nonnull_by.php',
        'Psl\Dict\filter_nulls' => __DIR__ . '/Dict/filter_nulls.php',
        'Psl\Dict\filter_with_key' => __DIR__ . '/Dict/filter_with_key.php',
        'Psl\Dict\flatten' => __DIR__ . '/Dict/flatten.php',
        'Psl\Dict\flip' => __DIR__ . '/Dict/flip.php',
        'Psl\Dict\from_entries' => __DIR__ . '/Dict/from_entries.php',
        'Psl\Dict\from_iterable' => __DIR__ . '/Dict/from_iterable.php',
        'Psl\Dict\from_keys' => __DIR__ . '/Dict/from_keys.php',
        'Psl\Dict\group_by' => __DIR__ . '/Dict/group_by.php',
        'Psl\Dict\intersect' => __DIR__ . '/Dict/intersect.php',
        'Psl\Dict\intersect_by_key' => __DIR__ . '/Dict/intersect_by_key.php',
        'Psl\Dict\map' => __DIR__ . '/Dict/map.php',
        'Psl\Dict\map_keys' => __DIR__ . '/Dict/map_keys.php',
        'Psl\Dict\map_nonnull' => __DIR__ . '/Dict/map_nonnull.php',
        'Psl\Dict\map_with_key' => __DIR__ . '/Dict/map_with_key.php',
        'Psl\Dict\merge' => __DIR__ . '/Dict/merge.php',
        'Psl\Dict\partition' => __DIR__ . '/Dict/partition.php',
        'Psl\Dict\partition_with_key' => __DIR__ . '/Dict/partition_with_key.php',
        'Psl\Dict\pull' => __DIR__ . '/Dict/pull.php',
        'Psl\Dict\pull_with_key' => __DIR__ . '/Dict/pull_with_key.php',
        'Psl\Dict\reindex' => __DIR__ . '/Dict/reindex.php',
        'Psl\Dict\select_keys' => __DIR__ . '/Dict/select_keys.php',
        'Psl\Dict\slice' => __DIR__ . '/Dict/slice.php',
        'Psl\Dict\sort' => __DIR__ . '/Dict/sort.php',
        'Psl\Dict\sort_by' => __DIR__ . '/Dict/sort_by.php',
        'Psl\Dict\sort_by_key' => __DIR__ . '/Dict/sort_by_key.php',
        'Psl\Dict\take' => __DIR__ . '/Dict/take.php',
        'Psl\Dict\take_while' => __DIR__ . '/Dict/take_while.php',
        'Psl\Dict\unique' => __DIR__ . '/Dict/unique.php',
        'Psl\Dict\unique_by' => __DIR__ . '/Dict/unique_by.php',
        'Psl\Dict\unique_scalar' => __DIR__ . '/Dict/unique_scalar.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
