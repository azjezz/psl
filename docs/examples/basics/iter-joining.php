<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Comparison\Order;
use Psl\EitherOrBoth;
use Psl\Iter;
use Psl\Vec;

// -- Sorted inputs: merge_join_by
// Both inputs must already be sorted according to the comparator. Lazy, O(1) memory
// on first traversal. The returned `Iter\Iterator` is rewindable.
$events = Vec\map::<int, EitherOrBoth\EitherOrBoth<int, int>, string>(
    Iter\merge_join_by::<int, int>([1, 2, 4], [2, 3, 4], static fn(int $a, int $b): Order => Order::from($a <=> $b)),
    static fn(EitherOrBoth\EitherOrBoth<int, int> $e): string => $e->proceed::<string>(
        left: static fn(int $v): string => "left:{$v}",
        right: static fn(int $v): string => "right:{$v}",
        both: static fn(int $l, int $r): string => "both:{$l}={$r}",
    ),
);
// ['left:1', 'both:2=2', 'right:3', 'both:4=4']

// -- Keyed inputs: merge_join_by_key
// Inputs do not need to be sorted. O(|right|) memory. Ideal when records carry an id.

/** @var list<array{id: int, name: string}> $incoming */
$incoming = [['id' => 1, 'name' => 'Ada'], ['id' => 3, 'name' => 'Grace']];

/** @var list<array{id: int, name: string}> $current */
$current = [['id' => 1, 'name' => 'Ada (old)'], ['id' => 2, 'name' => 'Linus']];

$changelog = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, string>(
    Iter\merge_join_by_key::<array, int>(
        $incoming,
        $current,
        /** @param array{id: int, name: string} $r */
        static fn(array $r): int => $r['id'],
    ),
    /** @param EitherOrBoth\EitherOrBoth<array{id: int, name: string}, array{id: int, name: string}> $e */
    static fn(EitherOrBoth\EitherOrBoth<array, array> $e): string => $e->proceed::<string>(
        /** @param array{id: int, name: string} $r */
        left: static fn(array $r): string => "+ {$r['id']} {$r['name']}",
        /** @param array{id: int, name: string} $r */
        right: static fn(array $r): string => "- {$r['id']} {$r['name']}",
        /**
         * @param array{id: int, name: string} $n
         * @param array{id: int, name: string} $o
         */
        both: static fn(array $n, array $o): string => "~ {$n['id']} {$o['name']} -> {$n['name']}",
    ),
);

// ['~ 1 Ada (old) -> Ada', '- 2 Linus', '+ 3 Grace']
