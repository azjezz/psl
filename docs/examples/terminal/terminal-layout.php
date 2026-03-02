<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;

final class LayoutState {}

Async\main(static function (): int {
    $app = Terminal\Application::create(new LayoutState(), title: 'Layout Demo');

    $app->on(Event\Key::class, static function (Event\Key $event, LayoutState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
        }
    });

    return $app->run(static function (Terminal\Frame $frame, LayoutState $state): void {
        $buffer = $frame->buffer();

        // Vertical split: header (3 rows) + content (fill) + footer (1 row)
        [$header, $content, $footer] = Layout\vertical($frame, [
            Layout\fixed(3),
            Layout\fill(),
            Layout\fixed(1),
        ]);

        // Horizontal split: sidebar (20 cols) + main (fill)
        [$sidebar, $main] = Layout\horizontal($content, [
            Layout\fixed(20),
            Layout\fill(),
        ]);

        // Constraints can be wrapped with min/max
        // Layout\min(10, Layout\fill());     // fill, but at least 10
        // Layout\max(50, Layout\fill());     // fill, but at most 50

        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Header Area')]),
        ])->render($header, $buffer);

        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Sidebar')]),
        ])->render($sidebar, $buffer);

        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Main Content')]),
        ])->render($main, $buffer);

        Widget\Paragraph::new([
            Widget\Line::new([Widget\Span::raw('Footer')]),
        ])->render($footer, $buffer);
    });
});
