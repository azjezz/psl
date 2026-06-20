<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;
use Psl\Type;

$result = Result\wrap::<array>(
    /** @return array<string, string> */
    fn(): array => Json\typed::<array>('{"name":"Alice"}', Type\dict::<string, string>(Type\string(), Type\string())),
);

if ($result->isSucceeded()) {
    $data = $result->getResult();
} else {
    $error = $result->getThrowable();
}

// Get the value with a fallback
$data = $result->unwrapOr::<array>([]);
