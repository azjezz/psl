<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$vec = Type\vec(Type\shape([
    'user' => Type\string(),
    'comment' => Type\string(),
]));

$vec->assert([
    ['user' => 'john', 'comment' => 'hello'],
    ['user' => 'jane', 'comment' => 'world'],
]);
