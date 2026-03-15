<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\IO;
use Psl\Shell;
use Psl\Shell\ErrorOutputBehavior;

// Discard stderr (default)
$stdout = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    errorOutputBehavior: ErrorOutputBehavior::Discard,
);
IO\write_line('Discard:  stdout=%s', $stdout);

// Append stderr after stdout
$combined = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    errorOutputBehavior: ErrorOutputBehavior::Append,
);
IO\write_line('Append:   %s', $combined);

// Prepend stderr before stdout
$combined = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    errorOutputBehavior: ErrorOutputBehavior::Prepend,
);
IO\write_line('Prepend:  %s', $combined);

// Return only stderr, discard stdout
$stderr = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    errorOutputBehavior: ErrorOutputBehavior::Replace,
);
IO\write_line('Replace:  %s', $stderr);

// Pack both streams for later separation
$packed = Shell\execute(
    'php',
    ['-r', 'echo "out"; fwrite(STDERR, "err");'],
    errorOutputBehavior: ErrorOutputBehavior::Packed,
);
[$stdout, $stderr] = Shell\unpack($packed);
IO\write_line('Packed:   stdout=%s, stderr=%s', $stdout, $stderr);
