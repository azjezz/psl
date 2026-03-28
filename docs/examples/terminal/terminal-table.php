<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class TableState {}

$app = Terminal\Application::create(new TableState(), title: 'Table Demo');

$app->on(Event\Key::class, static function (Event\Key $event, TableState $_state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
    }
});

$app->run(static function (Terminal\Frame $frame, TableState $_state): void {
    Widget\Table::new()
        ->headers(['Name', 'Status', 'CPU'])
        ->widths([15, 10, 8])
        ->rows([
            [
                Widget\Span::raw('nginx'),
                Widget\Span::styled('running', Ansi\foreground(Color\green())),
                Widget\Span::raw('2.1%'),
            ],
            [
                Widget\Span::raw('postgres'),
                Widget\Span::styled('running', Ansi\foreground(Color\green())),
                Widget\Span::raw('5.3%'),
            ],
        ])
        ->highlight(0)
        ->highlightStyle(Ansi\foreground(Color\bright_white()), Ansi\background(Color\blue()))
        ->render($frame->rect(), $frame->buffer());
});
