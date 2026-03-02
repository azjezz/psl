<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Filesystem;
use Psl\IO;

$base = sys_get_temp_dir() . '/psl-fs-links-' . uniqid();
Filesystem\create_directory($base);

$target = $base . '/target.txt';
$symlink = $base . '/symlink.txt';
$hardlink = $base . '/hardlink.txt';

Filesystem\create_file($target);

Filesystem\create_symbolic_link($target, $symlink);
Filesystem\create_hard_link($target, $hardlink);

$resolved = Filesystem\read_symbolic_link($symlink);
IO\write_line('Symlink points to: %s', $resolved);
IO\write_line('Is symbolic link: %s', Filesystem\is_symbolic_link($symlink) ? 'true' : 'false');
IO\write_line('Hard link exists: %s', Filesystem\is_file($hardlink) ? 'true' : 'false');

Filesystem\delete_directory($base, recursive: true);
