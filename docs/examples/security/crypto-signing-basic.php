<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Signing;
use Psl\IO;

$keyPair = Signing\generate_key_pair();

$message = 'This message is authentic.';
$signature = Signing\sign($message, $keyPair->secretKey);

$valid = Signing\verify($signature, $message, $keyPair->publicKey);
IO\write_line('Signature valid: %s', $valid ? 'yes' : 'no');
// Signature valid: yes

$valid = Signing\verify($signature, 'tampered message', $keyPair->publicKey);
IO\write_line('Tampered valid: %s', $valid ? 'yes' : 'no');

// Tampered valid: no
