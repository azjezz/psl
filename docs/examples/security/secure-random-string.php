<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\SecureRandom;

// 32-character alphanumeric token
$token = SecureRandom\string(32);
IO\write_line('Token: %s', $token);

// Hex string
$hex = SecureRandom\string(16, '0123456789abcdef');
IO\write_line('Hex: %s', $hex);

// Numeric code (e.g. for SMS verification)
$code = SecureRandom\string(6, '0123456789');
IO\write_line('Code: %s', $code);
