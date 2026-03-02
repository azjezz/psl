<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Either;

/** @var Either\Either<string, int> $either */
$either = new Either\Right(42);

$message = $either->proceed(
    static fn(int $value): string => "Got value: {$value}",
    static fn(string $error): string => "Error: {$error}",
);

// 'Got value: 42'
