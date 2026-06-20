<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;

$awaitable = Async\run::<string>(static function (): string {
    throw new Exception('Something went wrong!');
});

$awaitable = $awaitable->catch::<string>(static fn($error) => $error->getMessage());

$result = $awaitable->await(); // 'Something went wrong!'
