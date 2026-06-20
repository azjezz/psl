<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Cache;
use Psl\DateTime\Duration;
use Psl\IO;

$store = new Cache\LocalStore(maxSize: 100);

// compute() returns cached value or computes it
$value = $store->compute::<string>('greeting', static fn(): string => 'Hello, World!');

IO\write_line('%s', $value);

// Second call returns cached value - computer is not invoked
$value = $store->compute::<string>('greeting', static fn(): string => 'This is never called');

IO\write_line('%s', $value); // Still "Hello, World!"

// With TTL - entry expires after 5 minutes
$store->compute::<array>(
    'user:42',
    /** @return array{name: string} */
    static fn(): array => ['name' => 'Alice'],
    Duration::minutes(5),
);

// update() always invokes the computer with the old value
$store->compute::<int>('counter', static fn(): int => 0);
$store->update::<int>('counter', static fn(null|int $old): int => ($old ?? 0) + 1);

/** @var int $counter */
$counter = $store->get('counter');
IO\write_line('Counter: %d', $counter);
