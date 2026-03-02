<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$dict = Type\dict(Type\string(), Type\shape([
    'title' => Type\string(),
    'content' => Type\string(),
]));
