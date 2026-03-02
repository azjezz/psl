<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\Base64;

$encoded = Base64\encode('Hello, World!');
// 'SGVsbG8sIFdvcmxkIQ=='

$decoded = Base64\decode($encoded);

// 'Hello, World!'
