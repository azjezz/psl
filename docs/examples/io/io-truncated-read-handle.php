<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// Read at most 5 bytes, silently discard the rest
$inner = new IO\MemoryHandle('hello world');
$handle = new IO\TruncatedReadHandle($inner, 5);

$handle->readAll(); // 'hello'
$handle->reachedEndOfDataSource(); // true

// The underlying handle still has data, but the truncated handle doesn't care
$inner->tryRead(); // ' world'
