<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Write to two handles simultaneously -- useful for logging, hashing, or mirroring
$primary = new IO\MemoryHandle();
$mirror = new IO\MemoryHandle();

$tee = new IO\TeeWriteHandle($primary, $mirror);
$tee->writeAll('hello world');

$primary->seek(0);
$primary->readAll(); // 'hello world'

$mirror->seek(0);
$mirror->readAll(); // 'hello world'
