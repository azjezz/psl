<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

$parsed = new Either\Right('42');

$result = $parsed->mapRight(static fn(string $v): int => (int) $v)->mapRight(static fn(int $v): int => $v * 2);
// Right(84)

$error = new Either\Left('invalid input');
$error->mapRight(static fn(string $v): int => (int) $v);

// Left('invalid input') -- unchanged
