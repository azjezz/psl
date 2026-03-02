<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\SecureRandom;

$id = SecureRandom\int(1, 1_000_000);
IO\write_line('Random ID: %d', $id);

// Full 64-bit range by default
$big = SecureRandom\int();
IO\write_line('Random big int: %d', $big);
