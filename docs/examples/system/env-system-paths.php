<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Env;
use Psl\IO;

$tmp = Env\temp_dir();
IO\write_line('Temp directory: %s', $tmp);
