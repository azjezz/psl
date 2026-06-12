<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;

/** @var Async\Awaitable<string> $promise */
$promise = Async\run(static fn() => 'hello world');

$result = $promise->then(fn(string $value) => ['value' => $value], fn(Throwable $e) => ['error' => $e->getMessage()]);

$result->await(); // ['value' => 'hello world']
