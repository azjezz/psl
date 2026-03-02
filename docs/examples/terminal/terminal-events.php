<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class EventState {}

Async\main(static function (): int {
    $app = Terminal\Application::create(new EventState(), title: 'Events Demo');

    $app->on(Event\Key::class, static function (Event\Key $event, EventState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
        }

        if ($event->is('enter')) { /* handle enter */
        }

        if ($event->is('up')) { /* handle up arrow */
        }
    });

    $app->on(Event\Mouse::class, static function (Event\Mouse $event, EventState $state): void {
        // $event->kind (Press, Release, Drag, ScrollUp, ScrollDown, Move)
        // $event->column, $event->row
    });

    $app->on(Event\Paste::class, static function (Event\Paste $event, EventState $state): void {
        // $event->text -- the pasted string
    });

    $app->on(Event\Resize::class, static function (Event\Resize $event, EventState $state): void {
        // $event->width, $event->height
    });

    return $app->run(static function (Terminal\Frame $frame, EventState $state): void {
        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Press Ctrl+C to exit')]),
        ])->render($frame->rect(), $frame->buffer());
    });
});
