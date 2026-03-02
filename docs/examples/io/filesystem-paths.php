<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Filesystem;
use Psl\IO;

$base = sys_get_temp_dir() . '/psl-fs-paths-' . uniqid();
Filesystem\create_directory($base . '/subdir', 0o755);
$file = $base . '/subdir/file.txt';
Filesystem\create_file($file);

// Resolve to absolute path (returns null if path does not exist)
$real = Filesystem\canonicalize($base . '/subdir/../subdir/file.txt');
IO\write_line('Canonical: %s', $real ?? 'null');

IO\write_line('Basename: %s', Filesystem\get_basename($file)); // "file.txt"
IO\write_line('Without ext: %s', Filesystem\get_basename($file, '.txt')); // "file"
IO\write_line('Directory: %s', Filesystem\get_directory($file));
IO\write_line('Extension: %s', Filesystem\get_extension($file) ?? 'null'); // "txt"

$noExt = $base . '/subdir/Makefile';
Filesystem\create_file($noExt);
IO\write_line('No extension: %s', Filesystem\get_extension($noExt) ?? 'null'); // null

Filesystem\delete_directory($base, recursive: true);
