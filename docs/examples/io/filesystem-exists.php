<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Filesystem;
use Psl\IO;

// Create temp directory and file for demonstration
$dir = sys_get_temp_dir() . '/psl-fs-check-' . uniqid();
Filesystem\create_directory($dir);
$file = $dir . '/test.txt';
Filesystem\create_file($file);
$link = $dir . '/test-link';
Filesystem\create_symbolic_link($file, $link);

IO\write_line('exists(file):       %s', Filesystem\exists($file) ? 'true' : 'false');
IO\write_line('is_file(file):      %s', Filesystem\is_file($file) ? 'true' : 'false');
IO\write_line('is_directory(dir):  %s', Filesystem\is_directory($dir) ? 'true' : 'false');
IO\write_line('is_symbolic_link:   %s', Filesystem\is_symbolic_link($link) ? 'true' : 'false');
IO\write_line('is_readable(file):  %s', Filesystem\is_readable($file) ? 'true' : 'false');
IO\write_line('is_writable(file):  %s', Filesystem\is_writable($file) ? 'true' : 'false');

Filesystem\delete_file($link);
Filesystem\delete_file($file);
Filesystem\delete_directory($dir);
