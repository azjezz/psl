<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class TimerState
{
    public int $ticks = 0;
}

Async\main(static function (): int {
    $app = Terminal\Application::create(new TimerState(), title: 'Interval Demo');

    $app->on(Event\Key::class, static function (Event\Key $event, TimerState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
        }
    });

    $app->interval(Duration::milliseconds(100), static function (TimerState $state): void {
        // runs every 100ms
        $state->ticks++;
    });

    return $app->run(static function (Terminal\Frame $frame, TimerState $state): void {
        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Ticks: ' . $state->ticks)]),
        ])->render($frame->rect(), $frame->buffer());
    });
});
