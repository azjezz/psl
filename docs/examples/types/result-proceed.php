<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;
use Psl\Json;
use Psl\Result;
use Psl\Type;

$message = Result\wrap::<array>(fn(): array => Json\typed::<array>('{"a":1,"b":2,"c":3}', Type\dict::<string, int>(Type\string(), Type\int())))
    ->proceed::<string>(
        fn(array $data): string => 'Parsed: ' . Iter\count::<int>($data) . ' items',
        fn(Throwable $e): string => 'Failed: ' . $e->getMessage(),
    );
