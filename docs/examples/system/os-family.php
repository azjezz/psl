<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\OS;

$family = OS\family();
IO\write_line('OS Family: %s', $family->name);
