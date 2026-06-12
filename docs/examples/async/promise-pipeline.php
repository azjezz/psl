<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\IO;
use Psl\Json;
use Psl\Type;

/** @var Async\Awaitable<string> $promise */
$promise = Async\run(static fn() => '{"name": "psl", "version": "3.0"}');

$processed = $promise
    ->map(fn(string $raw) => Json\decode($raw))
    ->map(
        fn(mixed $data) => Type\shape([
            'name' => Type\string(),
            'version' => Type\string(),
        ])->coerce($data),
    )
    ->map(fn(array $valid) => ['project' => $valid['name'], 'v' => $valid['version']])
    ->catch(fn(Throwable $e) => ['error' => $e->getMessage()])
    ->always(fn() => IO\write_error_line('pipeline complete'));

$processed->await();
