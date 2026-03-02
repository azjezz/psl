<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;

$awaitable = Async\run(static function (): string {
    Async\sleep(Duration::seconds(1));
    return 'Hello world!';
});

$result = $awaitable->await(); // 'Hello world!'
