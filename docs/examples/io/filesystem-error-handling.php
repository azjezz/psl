<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Filesystem;
use Psl\IO;

$missing = sys_get_temp_dir() . '/psl-fs-missing-' . uniqid() . '.txt';

try {
    Filesystem\delete_file($missing);
} catch (Filesystem\Exception\NotFoundException) {
    IO\write_line('Caught NotFoundException: file does not exist');
} catch (Filesystem\Exception\NotFileException) {
    IO\write_line('Caught NotFileException: path exists but is not a file');
}
