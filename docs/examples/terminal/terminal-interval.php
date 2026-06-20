<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DateTime\Duration;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class TimerState
{
    public int $ticks = 0;
}

$app = Terminal\Application::create::<TimerState>(new TimerState(), title: 'Interval Demo');

$app->on::<Event\Key>(Event\Key::class, static function (Event\Key $event, TimerState $_state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
    }
});

$app->interval(Duration::milliseconds(100), static function (TimerState $state): void {
    // runs every 100ms
    $state->ticks++;
});

$app->run(static function (Terminal\Frame $frame, TimerState $state): void {
    Widget\Paragraph::new([
        Widget\Line::new([Widget\Span::raw('Ticks: ' . $state->ticks)]),
    ])->render($frame->rect(), $frame->buffer());
});
