<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$shape = Type\shape([
    'name' => Type\string(),
    'nickname' => Type\nullable(Type\string()), // present but may be null
    'bio' => Type\optional(Type\string()), // key may be missing entirely
]);
