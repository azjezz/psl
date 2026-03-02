<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$tmp = Filesystem\create_temporary_file(prefix: 'psl_meta_');
File\write($tmp, 'some content here');

$bytes = Filesystem\file_size($tmp);
$mtime = Filesystem\get_modification_time($tmp);
$perms = Filesystem\get_permissions($tmp);
$owner = Filesystem\get_owner($tmp);

IO\write_line('Size: %d bytes', $bytes);
IO\write_line('Modified: %s', date('Y-m-d H:i:s', $mtime));
IO\write_line('Permissions: %o', $perms);
IO\write_line('Owner UID: %d', $owner);

Filesystem\change_permissions($tmp, 0o644);
IO\write_line('New permissions: %o', Filesystem\get_permissions($tmp));

Filesystem\delete_file($tmp);
