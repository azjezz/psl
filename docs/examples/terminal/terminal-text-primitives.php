<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class TextState {}

Async\main(static function (): int {
    $app = Terminal\Application::create(new TextState(), title: 'Text Primitives Demo');

    $app->on(Event\Key::class, static function (Event\Key $event, TextState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
        }
    });

    return $app->run(static function (Terminal\Frame $frame, TextState $state): void {
        // Span -- a styled text fragment (the smallest text unit)
        $plain = Widget\Span::raw('plain text');
        $boldRed = Widget\Span::styled('bold red', Ansi\foreground(Color\red()), Style\bold());

        // Line -- a horizontal sequence of spans
        $line = Widget\Line::new([
            Widget\Span::styled('[INFO] ', Ansi\foreground(Color\blue())),
            Widget\Span::raw('Server started on port 8080'),
        ]);

        Widget\Paragraph::new([
            Widget\Line::new([$plain]),
            Widget\Line::new([$boldRed]),
            $line,
        ])->render($frame->rect(), $frame->buffer());
    });
});
