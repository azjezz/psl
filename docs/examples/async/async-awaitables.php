<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Str;

$awaitable = Async\run::<string>(static fn() => 'hello');

// Chain transformations
$awaitable = $awaitable->map::<string>(static fn($result) => Str\format('%s world', $result));

$result = $awaitable->await(); // 'hello world'
