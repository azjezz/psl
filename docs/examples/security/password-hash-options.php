<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Password;

$plaintext = 'my-secret-password';

// Bcrypt with a higher cost factor (default is 10)
$hash = Password\hash($plaintext, Password\Algorithm::Bcrypt, ['cost' => 14]);
IO\write_line('Bcrypt (cost 14): %s', $hash);
