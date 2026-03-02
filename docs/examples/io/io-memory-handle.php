<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$handle = new IO\MemoryHandle();
$handle->writeAll('Hello, World!');
$handle->seek(0);
$handle->readAll(); // 'Hello, World!'
$_ = $handle->getBuffer(); // 'Hello, World!' (always returns full buffer)

// Use MemoryHandle as a test double for any WriteHandleInterface
function render_report(IO\WriteHandleInterface $output): void
{
    $output->writeAll("Report\n");
}

$buffer = new IO\MemoryHandle();
render_report($buffer);
Psl\invariant($buffer->getBuffer() === "Report\n", 'Expected buffer to contain "Report\n"');
