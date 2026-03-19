<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Compression;
use Psl\IO;

// CompressingReadHandle wraps a readable handle and compresses data on read.
// DecompressingReadHandle does the reverse -- decompresses data on read.
// Both implement ReadHandleInterface. Wrap with IO\Reader for buffered methods.

$source = new IO\MemoryHandle('hello world');

/** @var Compression\CompressorInterface $compressor */
$handle = new Compression\CompressingReadHandle($source, $compressor);

$compressed = $handle->readAll();

IO\write_line('Compressed: %s', $compressed);
