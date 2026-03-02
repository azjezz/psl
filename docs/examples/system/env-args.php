<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Env;
use Psl\IO;

$args = Env\args();
IO\write_line('Arguments: %s', implode(', ', $args));

$binary = Env\current_exec();
IO\write_line('Executable: %s', $binary);
