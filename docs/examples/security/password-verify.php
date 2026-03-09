<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Password;

$plaintext = 'my-secret-password';
$storedHash = Password\hash($plaintext, Password\Algorithm::Bcrypt);

if (Password\verify($plaintext, $storedHash)) {
    IO\write_line('Password is correct.');
} else {
    IO\write_line('Password is incorrect.');
}
