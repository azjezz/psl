<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$dict = Type\dict::<string, array>(Type\string(), Type\shape::<string, string>([
    'title' => Type\string(),
    'content' => Type\string(),
]));
