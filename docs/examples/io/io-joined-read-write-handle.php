<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Join separate read and write handles into one bidirectional handle
$reader = new IO\MemoryHandle('incoming data');
$writer = new IO\MemoryHandle();

$handle = new IO\JoinedReadWriteHandle($reader, $writer);

$handle->readAll(); // 'incoming data' (from reader)
$handle->writeAll('outgoing data'); // written to writer
$writer->seek(0);
$writer->readAll(); // 'outgoing data'
