<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\SecureRandom;

$key = SecureRandom\bytes(32); // 32 random bytes for an AES-256 key
IO\write_line('Key length: %d bytes', strlen($key));
IO\write_line('Key (hex): %s', bin2hex($key));

$iv = SecureRandom\bytes(16); // 16 random bytes for an IV
IO\write_line('IV (hex): %s', bin2hex($iv));
