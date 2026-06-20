<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Shell;

Async\all::<int, string>([
    Async\run::<string>(static fn() => Shell\execute('echo', ['tests passed'])),
    Async\run::<string>(static fn() => Shell\execute('echo', ['analysis passed'])),
    Async\run::<string>(static fn() => Shell\execute('echo', ['formatting ok'])),
]);
