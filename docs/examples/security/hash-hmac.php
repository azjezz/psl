<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Hash\Hmac;
use Psl\IO;

$message = 'Hello, HMAC!';
$secretKey = 'my-secret-key-for-signing';

$signature = Hmac\hash(data: $message, algorithm: Hmac\Algorithm::Sha256, key: $secretKey);

IO\write_line('HMAC-SHA256: %s', $signature);
