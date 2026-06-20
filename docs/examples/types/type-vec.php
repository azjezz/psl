<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$vec = Type\vec::<array>(Type\shape::<string, string>([
    'user' => Type\string(),
    'comment' => Type\string(),
]));

$vec->assert([
    ['user' => 'john', 'comment' => 'hello'],
    ['user' => 'jane', 'comment' => 'world'],
]);
