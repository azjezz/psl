<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\IO;
use Psl\Str;

/** @var Async\Awaitable<string> $promise */
$promise = Async\run(static fn() => 'hello');

$promise
    ->map(fn(string $content) => Str\uppercase($content))
    ->always(fn() => IO\write_error_line('cleanup complete'))
    ->await();
