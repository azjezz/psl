<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\RandomSequence\MersenneTwisterSequence;

$seq = new MersenneTwisterSequence(seed: 42);

$first = $seq->next(); // always the same for seed 42
$second = $seq->next(); // deterministic

IO\write_line('First (seed 42): %d', $first);
IO\write_line('Second (seed 42): %d', $second);
