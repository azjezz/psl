<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;
use Psl\Result;
use Psl\Type;

$result = Result\wrap(
    /** @return array<string, string> */
    fn(): array => Json\typed('{"name":"Alice"}', Type\dict(Type\string(), Type\string())),
);

if ($result->isSucceeded()) {
    $data = $result->getResult();
} else {
    $error = $result->getThrowable();
}

// Get the value with a fallback
$data = $result->unwrapOr([]);
