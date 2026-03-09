<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Crypto\Signing;
use Psl\IO;

$keyPair = Signing\generate_key_pair();

// The Signer and Verifier classes are useful for dependency injection.
$signer = new Signing\Signer($keyPair->secretKey);
$verifier = new Signing\Verifier($keyPair->publicKey);

$signature = $signer->sign('Important document contents');
$valid = $verifier->verify($signature, 'Important document contents');

IO\write_line('Valid: %s', $valid ? 'yes' : 'no');
