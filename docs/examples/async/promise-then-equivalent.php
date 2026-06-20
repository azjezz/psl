<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;

/** @var Async\Awaitable<string> $promise */
$promise = Async\run::<string>(static fn() => 'hello world');

$result = $promise->map::<array>(fn(string $value) => ['value' => $value])->catch::<array>(fn(Throwable $e) => [
    'error' => $e->getMessage(),
]);

$result->await(); // ['value' => 'hello world']
