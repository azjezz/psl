<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Password;

$plaintext = 'my-secret-password';

// Uses the default algorithm (currently bcrypt, may change in future PHP versions)
$hash = Password\hash($plaintext);
IO\write_line('Default hash: %s', $hash);

// Specify an algorithm explicitly
$bcryptHash = Password\hash($plaintext, Password\Algorithm::Bcrypt);
IO\write_line('Bcrypt hash: %s', $bcryptHash);
