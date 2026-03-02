<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\IO;

$tmp = Filesystem\create_temporary_file(prefix: 'psl_lock_');
$handle = File\open_read_write($tmp);

try {
    $lock = $handle->tryLock(File\LockType::Exclusive);
    IO\write_line('Lock acquired successfully');
    $lock->release();
} catch (File\Exception\AlreadyLockedException) {
    IO\write_line('Another process holds the lock');
}

$handle->close();
Filesystem\delete_file($tmp);
