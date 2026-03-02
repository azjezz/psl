<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Env;
use Psl\IO;

$dirs = Env\split_paths('/usr/local/bin:/usr/bin:/bin');
IO\write_line('Split paths: %s', implode(', ', $dirs));

$path = Env\join_paths('/usr/local/bin', '/usr/bin', '/bin');
IO\write_line('Joined path: %s', $path);
