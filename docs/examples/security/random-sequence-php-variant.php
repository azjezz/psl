<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\RandomSequence\MersenneTwisterPHPVariantSequence;

$seq = new MersenneTwisterPHPVariantSequence(seed: 42);

$first = $seq->next();
$second = $seq->next();

IO\write_line('PHP variant first (seed 42): %d', $first);
IO\write_line('PHP variant second (seed 42): %d', $second);
