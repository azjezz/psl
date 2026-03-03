<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Symmetric;
use Psl\IO;

$key = Symmetric\generate_key();

$ciphertext = Symmetric\seal('Hello, World!', $key);
$plaintext = Symmetric\open($ciphertext, $key);

IO\write_line('Decrypted: %s', $plaintext);

// Decrypted: Hello, World!
