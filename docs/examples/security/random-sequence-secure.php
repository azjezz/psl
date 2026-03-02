<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\RandomSequence\SecureSequence;

$seq = new SecureSequence();
$value = $seq->next(); // cryptographically secure random int

IO\write_line('Secure random: %d', $value);
