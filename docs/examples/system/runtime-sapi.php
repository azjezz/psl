<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Runtime;

$sapi = Runtime\get_sapi();
IO\write_line('SAPI: %s', $sapi);
