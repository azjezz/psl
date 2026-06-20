<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Result;
use Psl\Shell;

[$version, $foo] = Async\concurrently::<int, Result\ResultInterface<string>>([
    Result\reflect::<string>(static fn() => Shell\execute('php', ['-v'])),
    Result\reflect::<string>(static fn() => Shell\execute('php', ['-r', 'foo();'])),
]);

// $version->isSucceeded() === true
// $foo->isFailed() === true
