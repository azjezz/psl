<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$source = new IO\MemoryHandle(str_repeat('x', 100_000));
$destination = new IO\MemoryHandle();

// Copy using 4 KB chunks instead of the default 8 KB
$bytesCopied = IO\copy_chunked($source, $destination, 4096);

$bytesCopied; // 100000
