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

/**
 * @mago-ignore best-practices/no-empty-catch-clause
 */
Async\main(static function (): int {
    if (PHP_OS_FAMILY === 'Windows') {
        IO\write_error_line('This example does not support Windows.');

        return 0;
    }

    $args = getopt('i:o:t:');
    $input_file = $args['i'] ?? '/dev/zero';
    $output_file = $args['o'] ?? '/dev/null';
    $seconds = DateTime\Duration::seconds((int) ($args['t'] ?? 5));

    // passing file descriptors requires mapping paths (https://bugs.php.net/bug.php?id=53465)
    $input_file = Regex\replace($input_file, '(^/dev/fd/)', 'php://fd/');
    $output_file = Regex\replace($output_file, '(^/dev/fd/)', 'php://fd/');

    $input = new IO\CloseReadStreamHandle(fopen($input_file, 'rb'));
    $output = new IO\CloseWriteStreamHandle(fopen($output_file, 'wb'));

    IO\write_error_line(
        'piping from %s to %s (for max %d second(s)) ...',
        $input_file,
        $output_file,
        $seconds->getTotalSeconds(),
    );

    Async\Scheduler::delay($seconds, static fn(): null => $input->close());

    $start = DateTime\Timestamp::monotonic();
    $i = 0;
    try {
        do {
            $chunk = $input->read(65536);
            if ('' === $chunk) {
                break;
            }

            $output->writeAll($chunk);
            $i++;

            Async\later();
        } while (true);
    } catch (IO\Exception\AlreadyClosedException) {
    }

    $duration = DateTime\Timestamp::monotonic()->since($start);
    $bytes = $i * 65536;
    $bytes_formatted = Math\round((($bytes / 1024) / 1024) / $duration->getTotalSeconds(), 1);

    IO\write_error_line('read %d byte(s) in %s => %dMiB/s', $bytes, $duration->toString(), $bytes_formatted);
    IO\write_error_line('peak memory usage of %dMiB', Math\round((memory_get_peak_usage(true) / 1024) / 1024, 1));

    return 0;
});
