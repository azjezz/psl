<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Hash;
use Psl\IO;

$key = 'my-hmac-secret-key';
$headerBytes = 'Content-Type: application/json';
$bodyBytes = '{"user":"alice","action":"login"}';

$digest = Hash\Context::hmac(Hash\Hmac\Algorithm::Sha256, $key)
    ->update($headerBytes)
    ->update($bodyBytes)
    ->finalize();

IO\write_line('Incremental HMAC: %s', $digest);
