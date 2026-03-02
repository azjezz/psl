<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\RandomSequence\SecureSequence;
use Psl\RandomSequence\SequenceInterface;

/** @var SequenceInterface $sequence */
$sequence = new SecureSequence();
$a = $sequence->next(); // next random int
$b = $sequence->next(); // another random int

IO\write_line('First: %d', $a);
IO\write_line('Second: %d', $b);
