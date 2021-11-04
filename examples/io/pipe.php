<?php

declare(strict_types=1);

namespace Psl\Example\IO;

use Psl\Async;
use Psl\IO;

require __DIR__ . '/../../vendor/autoload.php';

[$read, $write] = IO\pipe();

$output = IO\output_handle();

Async\concurrently([
    static function() use($read, $output): void {
        $output->writeAll("< sleeping.\n");

        Async\usleep(100);

        $output->writeAll("< waiting for content.\n");

        $content = $read->readAll();

        $output->writeAll("< received '$content'.\n");
        $output->writeAll("< closing.\n");

        $read->close();
    },
    static function() use($write, $output): void {
        $output->writeAll("> sleeping.\n");

        Async\usleep(10000);

        $output->writeAll("> writing.\n");

        $write->writeAll('hello, world');

        $output->write("> written 'hello, world'.\n");

        $output->write("> closing'.\n");

        $write->close();
    },
])->await();
