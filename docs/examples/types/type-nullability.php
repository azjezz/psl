<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Type;

$shape = Type\shape::<string, string|null>([
    'name' => Type\string(),
    'nickname' => Type\nullable::<string>(Type\string()), // present but may be null
    'bio' => Type\optional::<string>(Type\string()), // key may be missing entirely
    'avatar' => Type\nullish::<string>(Type\string()), // key may be missing, defaults to null
]);
