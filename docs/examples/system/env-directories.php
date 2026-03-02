<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Env;
use Psl\IO;

$cwd = Env\current_dir();
IO\write_line('Current directory: %s', $cwd);
