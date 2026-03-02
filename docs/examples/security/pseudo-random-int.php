<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\PseudoRandom;

$roll = PseudoRandom\int(1, 6);
IO\write_line('Dice roll: %d', $roll);

// Full 64-bit range by default
$value = PseudoRandom\int();
IO\write_line('Random value: %d', $value);
