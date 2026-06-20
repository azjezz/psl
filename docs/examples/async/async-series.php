<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;

$results = Async\series::<int, string>([
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'users created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'organizations created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'roles created';
    },
    static function (): string {
        Async\sleep(Duration::milliseconds(50));
        return 'user organization roles created';
    },
]);
