<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class ParagraphState {}

$app = Terminal\Application::create(new ParagraphState(), title: 'Paragraph Demo');

$app->on(Event\Key::class, static function (Event\Key $event, ParagraphState $_state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
    }
});

$app->run(static function (Terminal\Frame $frame, ParagraphState $_state): void {
    Widget\Paragraph::new([
        Widget\Line::new([
            Widget\Span::styled('Error: ', Ansi\foreground(Color\red()), Style\bold()),
            Widget\Span::raw('something went wrong'),
        ]),
        Widget\Line::new([Widget\Span::raw('Check the logs for details.')]),
    ])
        ->wrap(Widget\Wrap::Word)
        ->alignment(Widget\Alignment::Left)
        ->scroll(0)
        ->render($frame->rect(), $frame->buffer());
});
