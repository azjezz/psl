<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Fun;

$handler = Fun\rethrow();

try {
    $handler(new RuntimeException('boom'));
} catch (RuntimeException $e) {
    echo $e->getMessage(); // 'boom'
}
