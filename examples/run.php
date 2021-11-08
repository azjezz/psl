<?php


declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\Filesystem;
use Psl\IO;
use Psl\Shell;

require __DIR__ . '/../vendor/autoload.php';

Async\main(static function (): int {
    $output = IO\output_handle();

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

            $output->writeAll("- $component/$script   -> started\n");

            $awaitables[] = Async\run(static function() use($component, $script, $file): array {
                $start = microtime(true);
                Shell\execute(PHP_BINARY, [$file]);
                $duration = microtime(true) - $start;

                return [$component, $script, $duration];
            });
        }
    }

    foreach (Async\Awaitable::iterate($awaitables) as $awaitable) {
        [$component, $script, $duration] = $awaitable->await();

        $output->writeAll("+ $component/$script   -> finished in {$duration}s\n");
    }

    return 0;
});
