<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Filesystem;
use Psl\IO;

$dir = sys_get_temp_dir() . '/psl-fs-readdir-' . uniqid();
Filesystem\create_directory($dir);
Filesystem\create_file($dir . '/alpha.txt');
Filesystem\create_file($dir . '/beta.txt');
Filesystem\create_directory($dir . '/subdir');

$entries = Filesystem\read_directory($dir);
// Returns a list of absolute path strings (excludes . and ..)
foreach ($entries as $entry) {
    IO\write_line('  %s', Filesystem\get_basename($entry));
}

Filesystem\delete_directory($dir, recursive: true);
