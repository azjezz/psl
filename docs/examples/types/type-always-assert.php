<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;
use Psl\Type\Exception\CoercionException;

$integer = Type\int();
$strictInteger = Type\always_assert(Type\int());

$integer->coerce('1'); // 1 (coerced from string)

try {
    $strictInteger->coerce('1'); // CoercionException!
} catch (CoercionException $e) {
    echo $e->getMessage() . "\n";
}
