<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Cache;
use Psl\DateTime\Duration;
use Psl\IO;

$store = new Cache\LocalStore();

// Two fibers requesting the same key concurrently.
// KeyedSequence ensures only one computes - the other waits and gets the cached result.
[$a, $b] = Async\concurrently::<int, string>([
    static fn(): string => $store->compute::<string>('expensive', static function (): string {
        Async\sleep(Duration::milliseconds(100));

        return 'computed once';
    }),
    static fn(): string => $store->compute::<string>('expensive', static fn(): string => 'this is never called'),
]);

IO\write_line('Fiber A: %s', $a); // "computed once"
IO\write_line('Fiber B: %s', $b); // "computed once" - got cached result

// Different keys run in parallel - no blocking
Async\concurrently::<int, string>([
    static fn(): string => $store->compute::<string>('key-1', static fn(): string => 'value-1'),
    static fn(): string => $store->compute::<string>('key-2', static fn(): string => 'value-2'),
]);
