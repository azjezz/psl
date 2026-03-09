<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$base = sys_get_temp_dir() . '/psl-fs-copy-' . uniqid();
Filesystem\create_directory($base);

$src = $base . '/source.txt';
$dst = $base . '/dest.txt';
File\write($src, 'hello world');

// Copy a file (preserves executable bits)
Filesystem\copy($src, $dst);
IO\write_line('Copied: %s', File\read($dst));

// Copy with overwrite
File\write($src, 'updated content');
Filesystem\copy($src, $dst, overwrite: true);
IO\write_line('Overwritten: %s', File\read($dst));

// Delete a file
Filesystem\delete_file($dst);
IO\write_line('File deleted: %s', !Filesystem\exists($dst) ? 'yes' : 'no');

// Delete a directory (empty, or recursive)
$subDir = $base . '/subdir';
Filesystem\create_directory($subDir);
Filesystem\delete_directory($subDir);

// Delete a directory with contents recursively
Filesystem\delete_directory($base, recursive: true);
IO\write_line('Directory deleted: %s', !Filesystem\exists($base) ? 'yes' : 'no');
