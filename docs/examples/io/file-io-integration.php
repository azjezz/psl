<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$sourceFile = Filesystem\create_temporary_file(prefix: 'psl_src_');
$destFile = Filesystem\create_temporary_file(prefix: 'psl_dst_');

File\write($sourceFile, 'binary data to copy');

$source = File\open_read_only($sourceFile);
$dest = File\open_write_only($destFile, File\WriteMode::Truncate);
IO\copy($source, $dest);
$source->close();
$dest->close();

IO\write_line('Copied: %s', File\read($destFile));

Filesystem\delete_file($sourceFile);
Filesystem\delete_file($destFile);
