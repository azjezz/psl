<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\PseudoRandom;

$chance = PseudoRandom\float();
IO\write_line('Random chance: %f', $chance);

if ($chance < 0.3) {
    IO\write_line('Hit the 30%% probability branch!');
} else {
    IO\write_line('Missed the 30%% probability branch.');
}
