<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\Base64;

$encoded = Base64\encode('Hello!', padding: false);
// 'SGVsbG8h' (no trailing '=')

$decoded = Base64\decode($encoded, explicitPadding: false);

// 'Hello!'
