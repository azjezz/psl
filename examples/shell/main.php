<?php

declare(strict_types=1);

namespace Psl\Example\Shell;

use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../../vendor/autoload.php';

$result = Shell\execute('echo', ['hello']);

IO\output_handle()->writeAll("result: $result.\n");
