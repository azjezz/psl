<?php

declare(strict_types=1);

namespace Psl\Example\Terminal;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\DateTime;
use Psl\Iter;
use Psl\Math;
use Psl\PseudoRandom;
use Psl\Str;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;

require __DIR__ . '/../../vendor/autoload.php';

final class Particle
{
    public function __construct(
        public int $x,
        public int $y,
        public int $age,
        public string $char,
    ) {}
}

/** @var list<string> */
const SPARKLE_CHARS = ['✦', '✧', '⋆', '·', '˚', '*', '✹', '❋', '⁕', '∘'];

final class DustState
{
    /** @var list<Particle> */
    public array $particles = [];
    public int $total_spawned = 0;
    public float $displayedFps = 0.0;
    public int $frameCount = 0;
    public null|DateTime\Timestamp $lastFpsUpdate = null;
}

/**
 * @return Color\Color
 */
function particle_color(int $age): Color\Color
{
    return match (true) {
        $age < 5 => Color\bright_white(),
        $age < 10 => Color\bright_cyan(),
        $age < 18 => Color\bright_yellow(),
        $age < 25 => Color\bright_magenta(),
        default => Color\ansi256(240),
    };
}

Async\main(static function (): int {
    $app = Terminal\Application::create(new DustState(), title: 'Fairy Dust', mouseMotion: true);

    $app->on(Event\Key::class, static function (Event\Key $event, DustState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
            return;
        }

        if ($event->char === 'c') {
            $state->particles = [];
        }
    });

    $app->on(Event\Mouse::class, static function (Event\Mouse $event, DustState $state): void {
        if ($event->kind !== Event\MouseKind::Move && $event->kind !== Event\MouseKind::Drag) {
            return;
        }

        /** @var non-negative-int $idx */
        $idx = PseudoRandom\int(0, Iter\count(SPARKLE_CHARS) - 1);
        $char = SPARKLE_CHARS[$idx];
        $state->particles[] = new Particle($event->column, $event->row, 0, $char);
        $state->total_spawned++;
    });

    return $app->run(static function (Terminal\Frame $frame, DustState $state): void {
        $buffer = $frame->buffer();
        $now = DateTime\Timestamp::monotonic();
        $state->frameCount++;
        if ($state->lastFpsUpdate === null) {
            $state->lastFpsUpdate = $now;
        } else {
            $elapsed = $now->since($state->lastFpsUpdate)->getTotalSeconds();
            if ($elapsed >= 1.0) {
                $state->displayedFps = $state->frameCount / $elapsed;
                $state->frameCount = 0;
                $state->lastFpsUpdate = $now;
            }
        }

        $fps = $state->displayedFps;

        $alive = [];
        foreach ($state->particles as $particle) {
            $particle->age++;
            if ($particle->age < 30) {
                $alive[] = $particle;
            }
        }

        $state->particles = $alive;

        [$main, $statusBar] = Layout\vertical($frame, [
            Layout\fill(),
            Layout\fixed(1),
        ]);

        $block = Widget\Block::new()
            ->title(' Fairy Dust ')
            ->titleStyle(Ansi\foreground(Color\bright_magenta()), Style\bold())
            ->border(Widget\Border::rounded(Ansi\foreground(Color\ansi256(240))));

        $block->render($main, Widget\Paragraph::new([]), $buffer);
        $inner = $block->innerArea($main);

        foreach ($state->particles as $particle) {
            $px = $particle->x - $inner->x;
            $py = $particle->y - $inner->y;
            if ($px < 0 || $py < 0 || $px >= $inner->width || $py >= $inner->height) {
                continue;
            }

            $color = particle_color($particle->age);
            $buffer->set($particle->x, $particle->y, new Terminal\Cell($particle->char, [Ansi\foreground($color)]));
        }

        $count = Iter\count($state->particles);
        $rightText = 'Move your mouse! | c clear | Ctrl+C quit ';
        $rightLen = Str\width($rightText);

        [$statusLeft, $statusRight] = Layout\horizontal($statusBar, [
            Layout\fill(),
            Layout\fixed($rightLen),
        ]);

        $fpsRounded = Math\round($fps);
        $fpsStr = Str\format('%.0f', $fpsRounded);
        $fpsColor = match (true) {
            $fpsRounded >= 60.0 => Color\bright_green(),
            $fpsRounded >= 40.0 => Color\bright_yellow(),
            default => Color\bright_red(),
        };

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled(' ' . $fpsStr . ' fps', Ansi\foreground($fpsColor)),
            Widget\Span::styled(
                Str\format(' · %d particles · %d total', $count, $state->total_spawned),
                Ansi\foreground(Color\bright_black()),
            ),
        ])])->render($statusLeft, $buffer);

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled($rightText, Ansi\foreground(Color\bright_black())),
        ])])->alignment(Widget\Alignment::Right)->render($statusRight, $buffer);
    });
});
