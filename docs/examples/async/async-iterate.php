<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$handles = [
    Async\run::<string>(static function () {
        Async\sleep(Duration::seconds(1));
        return 'a';
    }),
    Async\run::<string>(static fn() => 'b'),
    Async\run::<string>(static function () {
        Async\sleep(Duration::milliseconds(300));
        return 'c';
    }),
    Async\run::<string>(static function () {
        Async\sleep(Duration::milliseconds(100));
        return 'd';
    }),
];

foreach (Async\Awaitable::iterate::<int, string>($handles) as $k => $awaitable) {
    $result = $awaitable->await();
    IO\write_line($k . ': ' . $result);
}

// Output:
// 1: b
// 3: d
// 2: c
// 0: a
