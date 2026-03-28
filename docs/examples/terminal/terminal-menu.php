<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Widget;

final class MenuState
{
    public int $selectedIndex = 0;
    public int $scrollOffset = 0;
}

$app = Terminal\Application::create(new MenuState(), title: 'Menu Demo');

$app->on(Event\Key::class, static function (Event\Key $event, MenuState $_state) use ($app): void {
    if ($event->is('ctrl+c')) {
        $app->stop();
    }
});

$app->run(static function (Terminal\Frame $frame, MenuState $state): void {
    Widget\Menu::new([
        Widget\MenuItem::raw('Open File'),
        Widget\MenuItem::raw('Save'),
        Widget\MenuItem::styled([
            Widget\Span::styled('Quit', Ansi\foreground(Color\red())),
        ]),
    ])
        ->highlight($state->selectedIndex)
        ->scroll($state->scrollOffset)
        ->highlightStyle(Ansi\foreground(Color\bright_white()), Ansi\background(Color\blue()))
        ->render($frame->rect(), $frame->buffer());
});
