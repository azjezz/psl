<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$counterFile = Filesystem\create_temporary_file(prefix: 'psl_counter_');
File\write($counterFile, '0');

$handle = File\open_read_write($counterFile);
$lock = $handle->lock(File\LockType::Exclusive);

$count = (int) $handle->readAll();
$handle->seek(0);
$handle->writeAll((string) ($count + 1));

$lock->release();
$handle->close();

IO\write_line('Counter incremented to: %s', File\read($counterFile));

Filesystem\delete_file($counterFile);
