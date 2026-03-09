<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$tmp = Filesystem\create_temporary_file(prefix: 'psl_file_');

// Write to a file (creates it if it does not exist)
File\write($tmp, '{"key": "value"}');

// Read an entire file
$content = File\read($tmp);
IO\write_line('Full content: %s', $content);

// Read with offset and length
$header = File\read($tmp, offset: 0, length: 6);
IO\write_line('Header: %s', $header);

// Overwrite with truncation
File\write($tmp, 'New content', File\WriteMode::Truncate);
IO\write_line('After truncate: %s', File\read($tmp));

Filesystem\delete_file($tmp);
