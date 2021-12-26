<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

Async\main(static function(): int {
    [$read, $write] = IO\pipe();

    Async\concurrently([
        static function() use($read): void {
            IO\write_error_line("< sleeping.");

            Async\sleep(0.01);

            IO\write_error_line("< waiting for content.");

            $content = $read->readAll();

            IO\write_error_line('< received "%s".', $content);
            IO\write_error_line("< closing.");

            $read->close();
        },
        static function() use($write): void {
            IO\write_error_line('> sleeping.');

            Async\sleep(0.1);

            IO\write_error_line('> writing.');

            $write->writeAll('hello, world');

            IO\write_error_line('> written "hello, world".');

            IO\write_error_line('> closing.');

            $write->close();
        },
    ]);

    return 0;
});
