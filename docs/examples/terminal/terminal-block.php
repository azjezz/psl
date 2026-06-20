<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class BlockState {}

$app = Terminal\Application::create::<BlockState>(new BlockState(), title: 'Block Demo');

$app->on::<Event\Key>(Event\Key::class, static function (Event\Key $event, BlockState $_state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
    }
});

$app->run(static function (Terminal\Frame $frame, BlockState $_state): void {
    $area = $frame->rect();
    $buffer = $frame->buffer();

    $block = Widget\Block::new()
        ->title(' Status ')
        ->titleStyle(Ansi\foreground(Color\bright_white()), Style\bold())
        ->border(Widget\Border::rounded(Ansi\foreground(Color\bright_cyan())))
        ->padding(right: 1, left: 1)
        ->margin(top: 1)
        ->background(Color\ansi256(235));

    $paragraph = Widget\Paragraph::new([
        Widget\Line::new([Widget\Span::raw('All systems operational.')]),
    ]);

    $block->render($area, $paragraph, $buffer);
});
