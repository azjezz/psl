<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;

$normalized = Result\wrap(fn() => Json\decode('{"name":"Alice","age":30}'))
    ->then(fn(mixed $data) => ['status' => 'ok', 'data' => $data], fn(Throwable $e) => ['error' => $e->getMessage()]);

// $normalized is ResultInterface<array>, still wrapped
