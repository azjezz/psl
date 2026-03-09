<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\Terminal;
use Psl\Terminal\Event;

final class BufferState {}

Async\main(static function (): int {
    $app = Terminal\Application::create(new BufferState(), title: 'Buffer Demo');

    $app->on(Event\Key::class, static function (Event\Key $event, BufferState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
        }
    });

    return $app->run(static function (Terminal\Frame $frame, BufferState $state): void {
        $buffer = $frame->buffer();

        // Direct cell manipulation
        $buffer->set(0, 0, new Terminal\Cell('X', [Ansi\foreground(Color\red())]));
        $buffer->setString(0, 1, 'Hello', [Ansi\foreground(Color\green()), Style\bold()]);

        // Read cells
        $cell = $buffer->get(0, 0);
        $grapheme = $cell?->grapheme;
    });
});
