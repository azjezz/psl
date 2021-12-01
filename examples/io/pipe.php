<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function(): int {
    [$read, $write] = IO\pipe();

    $output = IO\output_handle();

    Async\parallel([
        static function() use($read, $output): void {
            $output->writeAll("< sleeping.\n");

            Async\sleep(0.01);

            $output->writeAll("< waiting for content.\n");

            $content = $read->readAll();

            $output->writeAll("< received '$content'.\n");
            $output->writeAll("< closing.\n");

            $read->close();
        },
        static function() use($write, $output): void {
            $output->writeAll("> sleeping.\n");

            Async\sleep(0.1);

            $output->writeAll("> writing.\n");

            $write->writeAll('hello, world');

            $output->write("> written 'hello, world'.\n");

            $output->write("> closing'.\n");

            $write->close();
        },
    ]);

    return 0;
});
