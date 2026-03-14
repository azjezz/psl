<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;

$results = [];
$group = new Async\TaskGroup();

$group->defer(static function () use (&$results): void {
    Async\sleep(Duration::milliseconds(20));
    $results[] = 'slow';
});

$group->defer(static function () use (&$results): void {
    $results[] = 'fast';
});

$group->awaitAll();

IO\write_line('Order: %s', Psl\Str\join($results, ', ')); // "fast, slow"
