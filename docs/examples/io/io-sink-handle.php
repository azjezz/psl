<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

// SinkWriteHandle discards everything -- like /dev/null
$sink = new IO\SinkWriteHandle();
$sink->writeAll('this goes nowhere');
$sink->writeAll('neither does this');

// SinkReadWriteHandle also supports reads, but always reports EOF
$sink = new IO\SinkReadWriteHandle();
$sink->writeAll('discarded');
$sink->read(); // '' (always empty)
$sink->reachedEndOfDataSource(); // true (always EOF)
