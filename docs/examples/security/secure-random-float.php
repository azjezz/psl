<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\SecureRandom;

$probability = SecureRandom\float();
IO\write_line('Random float: %f', $probability);
