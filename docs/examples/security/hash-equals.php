<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Hash;
use Psl\IO;

$data = 'sensitive payload';
$expected = Hash\hash($data, Hash\Algorithm::Sha256);

$userProvidedHash = $expected; // simulate correct hash

// Timing-safe comparison
if (Hash\equals($expected, $userProvidedHash)) {
    IO\write_line('Hashes match.');
} else {
    IO\write_line('Hashes do not match.');
}
