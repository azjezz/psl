<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Password;

$plaintext = 'my-secret-password';
$storedHash = Password\hash($plaintext, Password\Algorithm::Bcrypt);

if (Password\verify($plaintext, $storedHash)) {
    IO\write_line('Password is correct.');

    // Check if the hash needs upgrading
    if (Password\needs_rehash($storedHash, Password\Algorithm::Bcrypt, ['cost' => 14])) {
        $newHash = Password\hash($plaintext, Password\Algorithm::Bcrypt, ['cost' => 14]);
        IO\write_line('Hash upgraded: %s', $newHash);
        // store $newHash in the database
    } else {
        IO\write_line('Hash is up to date.');
    }
}
