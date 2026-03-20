<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class MyState {}

$app = Terminal\Application::create(new MyState(), title: 'My App');

$app->on(Event\Key::class, static function (Event\Key $event, MyState $state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
    }
});

return $app->run(static function (Terminal\Frame $frame, MyState $state): void {
    $buffer = $frame->buffer();

    Widget\Paragraph::new([
        Widget\Line::new([Widget\Span::raw('Hello, World!')]),
    ])->render($frame->rect(), $buffer);
});
