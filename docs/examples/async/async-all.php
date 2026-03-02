<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Shell;

Async\all([
    Async\run(static fn() => Shell\execute('echo', ['tests passed'])),
    Async\run(static fn() => Shell\execute('echo', ['analysis passed'])),
    Async\run(static fn() => Shell\execute('echo', ['formatting ok'])),
]);
