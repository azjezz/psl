<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\KeyExchange;
use Psl\IO;

// Both parties generate key pairs
$alice = KeyExchange\generate_key_pair();
$bob = KeyExchange\generate_key_pair();

// Each party computes a shared secret using their secret key and the other's public key
$aliceShared = KeyExchange\agree($alice->secretKey, $bob->publicKey);
$bobShared = KeyExchange\agree($bob->secretKey, $alice->publicKey);

// Both arrive at the same shared secret
IO\write_line('Secrets match: %s', $aliceShared->bytes === $bobShared->bytes ? 'yes' : 'no');

// Secrets match: yes
