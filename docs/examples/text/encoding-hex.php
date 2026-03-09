<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\Exception;
use Psl\Encoding\Hex;

$hex = Hex\encode('Hello');
// '48656c6c6f'

$binary = Hex\decode('48656c6c6f');
// 'Hello'

// Invalid hex throws immediately
try {
    Hex\decode('xyz');
} catch (Exception\RangeException $e) {
    echo $e->getMessage() . "\n";
}

// Odd-length strings are rejected
try {
    Hex\decode('abc');
} catch (Exception\RangeException $e) {
    echo $e->getMessage() . "\n";
}
