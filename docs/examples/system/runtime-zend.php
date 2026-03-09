<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Runtime;

$zendVersion = Runtime\get_zend_version();
IO\write_line('Zend Engine version: %s', $zendVersion);
