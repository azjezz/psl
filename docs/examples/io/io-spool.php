<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Str;

// Writes to memory until 2MB, then spools to a temporary file on disk
$handle = IO\spool();

$handle->writeAll('Hello, World!');
$handle->seek(0);
$handle->readAll(); // 'Hello, World!'

$handle->close();

// Custom threshold: spool to disk after 64 bytes
$small = IO\spool(maxMemory: 64);
$small->writeAll(Str\repeat('x', 256)); // transparently written to disk
$small->seek(0);
$small->readAll(); // 256 bytes of 'x'

$small->close();
