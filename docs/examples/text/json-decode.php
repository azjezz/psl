<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Json;

$_data = Json\decode('{"name":"Alice","age":30}');
// ['name' => 'Alice', 'age' => 30]

// Invalid JSON throws immediately
try {
    Json\decode('not json');
} catch (Json\Exception\DecodeException $e) {
    echo $e->getMessage() . "\n";
}
