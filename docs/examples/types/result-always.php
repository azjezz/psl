<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\File;
use Psl\Filesystem;
use Psl\Result;

$temp = Filesystem\create_temporary_file();
File\write($temp, 'temporary data');

$result = Result\wrap(fn() => File\read($temp))->always(fn() => Filesystem\delete_file($temp));
