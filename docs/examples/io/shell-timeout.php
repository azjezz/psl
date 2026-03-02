<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Shell;

try {
    $output = Shell\execute('sleep', ['10'], timeout: Duration::seconds(1));
} catch (Shell\Exception\TimeoutException) {
    IO\write_line('Command exceeded the time limit (as expected)');
}
