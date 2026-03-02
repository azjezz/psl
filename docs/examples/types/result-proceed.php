<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;
use Psl\Json;
use Psl\Result;
use Psl\Type;

$message = Result\wrap(fn(): array => Json\typed('{"a":1,"b":2,"c":3}', Type\dict(Type\string(), Type\int())))
    ->proceed(
        fn(array $data): string => 'Parsed: ' . Iter\count($data) . ' items',
        fn(Throwable $e): string => 'Failed: ' . $e->getMessage(),
    );
