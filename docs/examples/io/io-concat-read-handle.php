<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Concatenate two handles into a single stream
$header = new IO\MemoryHandle("Header\n");
$body = new IO\MemoryHandle('Body content');

$combined = new IO\ConcatReadHandle($header, $body);
$combined->readAll(); // "Header\nBody content"
