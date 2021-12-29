<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\Async;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function (): void {
    $result = Shell\execute('echo', ['hello']);

    IO\write_error('result: %s', $result);
});
