<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;

$source = new IO\MemoryHandle('data to copy');
$dest = new IO\MemoryHandle();
IO\copy($source, $dest);

IO\write_line('Copied: %s', $dest->getBuffer()); // 'data to copy'
