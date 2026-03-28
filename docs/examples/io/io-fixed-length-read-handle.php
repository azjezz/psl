<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Read exactly 11 bytes
$inner = new IO\MemoryHandle('hello world');
$handle = new IO\FixedLengthReadHandle($inner, 11);
$handle->readAll(); // 'hello world'
$handle->reachedEndOfDataSource(); // true

// Premature EOF throws
$inner = new IO\MemoryHandle('hi');
$handle = new IO\FixedLengthReadHandle($inner, 10);

try {
    $handle->readAll(); // throws -- only 2 bytes available, expected 10
} catch (IO\Exception\RuntimeException $e) {
    $e->getMessage(); // 'Expected 10 bytes, but only 2 were available (premature EOF)'
}
