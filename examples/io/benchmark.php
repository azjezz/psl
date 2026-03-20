<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\DateTime;
use Psl\IO;
use Psl\Math;
use Psl\Regex;

use function fopen;
use function getopt;
use function memory_get_peak_usage;

use const PHP_OS_FAMILY;

require __DIR__ . '/../../vendor/autoload.php';

if (PHP_OS_FAMILY === 'Windows') {
    IO\write_error_line('This example does not support Windows.');

    return 0;
}

$args = getopt('i:o:t:');
$inputFile = $args['i'] ?? '/dev/zero';
$outputFile = $args['o'] ?? '/dev/null';
$seconds = DateTime\Duration::seconds((int) ($args['t'] ?? 5));

// passing file descriptors requires mapping paths (https://bugs.php.net/bug.php?id=53465)
$inputFile = Regex\replace($inputFile, '(^/dev/fd/)', 'php://fd/');
$outputFile = Regex\replace($outputFile, '(^/dev/fd/)', 'php://fd/');

$input = new IO\CloseReadStreamHandle(fopen($inputFile, 'rb'));
$output = new IO\CloseWriteStreamHandle(fopen($outputFile, 'wb'));

IO\write_error_line(
    'piping from %s to %s (for max %d second(s)) ...',
    $inputFile,
    $outputFile,
    $seconds->getTotalSeconds(),
);

Async\Scheduler::delay($seconds, $input->close(...));

$start = DateTime\Timestamp::monotonic();
$i = 0;
try {
    do {
        $chunk = $input->read(65_536);
        if ('' === $chunk) {
            break;
        }

        $output->writeAll($chunk);
        $i++;

        Async\later();
    } while (true);
} catch (IO\Exception\AlreadyClosedException) {
    // @mago-expect lint:no-empty-catch-clause
}

$duration = DateTime\Timestamp::monotonic()->since($start);
$bytes = $i * 65_536;
$bytesFormatted = Math\round((($bytes / 1024) / 1024) / $duration->getTotalSeconds(), 1);

IO\write_error_line('read %d byte(s) in %s => %dMiB/s', $bytes, $duration->toString(), $bytesFormatted);
IO\write_error_line('peak memory usage of %dMiB', Math\round((memory_get_peak_usage(true) / 1024) / 1024, 1));
