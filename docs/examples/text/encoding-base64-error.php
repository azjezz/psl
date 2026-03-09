<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Encoding\Base64;
use Psl\Encoding\Exception;

try {
    Base64\decode('not-valid-base64!');
} catch (Exception\RangeException $e) {
    echo $e->getMessage() . "\n";
}
