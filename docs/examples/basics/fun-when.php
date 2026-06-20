<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;

$classify = Fun\when::<int, string>(
    static fn(int $n): bool => $n >= 18,
    static fn(int $_n): string => 'adult',
    static fn(int $_n): string => 'minor',
);

$classify(21); // 'adult'
$classify(12); // 'minor'
