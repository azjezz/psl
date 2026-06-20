<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\EitherOrBoth;
use Psl\Iter;
use Psl\Vec;

/** @var list<array{id: int, name: string}> $incoming */
$incoming = [['id' => 1, 'name' => 'Ada'], ['id' => 3, 'name' => 'Grace']];

/** @var list<array{id: int, name: string}> $current */
$current = [['id' => 1, 'name' => 'Ada (old)'], ['id' => 2, 'name' => 'Linus']];

// 1. Side effects (DB sync). `Iter\apply` is a drop-in for a foreach loop when
// you do not need the loop variable after the iteration finishes.
$ops = [];
Iter\apply::<EitherOrBoth\EitherOrBoth<array, array>>(
    Iter\merge_join_by_key::<array, int>(
        $incoming,
        $current,
        /** @param array{id: int, name: string} $r */
        static fn(array $r): int => $r['id'],
    ),
    /** @param EitherOrBoth\EitherOrBoth<array{id: int, name: string}, array{id: int, name: string}> $event */
    static fn(EitherOrBoth\EitherOrBoth<array, array> $event): mixed => $event->proceed::<mixed>(
        /** @param array{id: int, name: string} $new */
        left: static fn(array $new) => $ops[] = ['insert', $new['id']],
        /** @param array{id: int, name: string} $old */
        right: static fn(array $old) => $ops[] = ['delete', $old['id']],
        /**
         * @param array{id: int, name: string} $new
         * @param array{id: int, name: string} $old
         */
        both: static fn(array $new, array $old) => $ops[] = ['update', $new['id'], $old['id']],
    ),
);
// $ops: [['update', 1, 1], ['delete', 2], ['insert', 3]]

// 2. Pure mapping (build a changelog line for each event)
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

// 3. Filtering before applying (only care about deletes)
$deletes = Vec\filter::<EitherOrBoth\EitherOrBoth<array, array>>(
    Vec\values::<EitherOrBoth\EitherOrBoth<array, array>>(Iter\merge_join_by_key::<array, int>(
        $incoming,
        $current,
        /** @param array{id: int, name: string} $r */
        static fn(array $r): int => $r['id'],
    )),
    static fn(EitherOrBoth\EitherOrBoth<array, array> $e): bool => $e->isRight(),
);

// 4. Mapping each side independently (hydrate old + new differently)
$hydrated = Vec\map::<int, EitherOrBoth\EitherOrBoth<array, array>, EitherOrBoth\EitherOrBoth<string, string>>(
    Iter\merge_join_by_key::<array, int>(
        $incoming,
        $current,
        /** @param array{id: int, name: string} $r */
        static fn(array $r): int => $r['id'],
    ),
    /** @param EitherOrBoth\EitherOrBoth<array{id: int, name: string}, array{id: int, name: string}> $e */
    static fn(EitherOrBoth\EitherOrBoth<array, array> $e): EitherOrBoth\EitherOrBoth<string, string> => $e->mapAny::<string, string>(
        /** @param array{id: int, name: string} $new */
        static fn(array $new): string => "NEW:{$new['name']}",
        /** @param array{id: int, name: string} $old */
        static fn(array $old): string => "OLD:{$old['name']}",
    ),
);
