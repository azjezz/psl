<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\DateTime;
use Psl\Filesystem;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../vendor/autoload.php';

Async\main(static function (): int {
    $awaitables = [];
    $folders = Filesystem\read_directory(__DIR__);
    foreach ($folders as $folder) {
        if (!Filesystem\is_directory($folder)) {
            continue;
        }

        $component = Filesystem\get_filename($folder);
        $files = Filesystem\read_directory($folder);

        foreach ($files as $file) {
            $script = Filesystem\get_filename($file);

            if ($script === 'basic-http-server') {
                // long running process.
                continue;
            }

            IO\write_error_line('- %s/%s   -> started', $component, $script);

            $awaitables[] = Async\run(static function () use ($component, $script, $file): array {
                $start = DateTime\Timestamp::monotonic();
                Shell\execute(PHP_BINARY, [$file]);
                $duration = DateTime\Timestamp::monotonic()->since($start);

                return [$component, $script, $duration];
            });
        }
    }

    foreach (Async\Awaitable::iterate($awaitables) as $awaitable) {
        [$component, $script, $duration] = $awaitable->await();

        IO\write_error_line('+ %s/%s   -> finished in %s', $component, $script, $duration->toString());
    }

    return 0;
});
