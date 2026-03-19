<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Compression;
use Psl\DateTime\Duration;
use Psl\IO;

// CompressingWriteHandle wraps a writable handle and compresses data on write.
// DecompressingWriteHandle does the reverse -- decompresses data on write.
// Both implement BufferedWriteHandleInterface. Call flush() to finalize.

$output = new IO\MemoryHandle();

/** @var Compression\CompressorInterface $compressor */
$writer = new Compression\CompressingWriteHandle($output, $compressor);

$writer->writeAll('hello world');
$writer->flush(); // finalize the compression stream

// flush() accepts an optional CancellationTokenInterface for timeout control
$writer->flush(new Async\TimeoutCancellationToken(Duration::seconds(5)));

$output->seek(0);
IO\write_line('Result: %s', $output->readAll());
