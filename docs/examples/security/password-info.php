<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Password;

$hash = Password\hash('my-secret-password', Password\Algorithm::Bcrypt);

$info = Password\get_information($hash);
IO\write_line('Algorithm: %s', $info['algorithm']->name);
IO\write_line('Cost: %d', $info['options']['cost'] ?? '<unknown>');
