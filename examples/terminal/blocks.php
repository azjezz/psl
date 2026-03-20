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
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;
use Psl\Vec;

require __DIR__ . '/../../vendor/autoload.php';

final class DragBlock
{
    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height,
        public Color\Color $color,
        public string $label,
        public bool $rainbow = false,
    ) {}

    public function contains(int $x, int $y): bool
    {
        return $x >= $this->x && $x < ($this->x + $this->width) && $y >= $this->y && $y < ($this->y + $this->height);
    }
}

function rainbow_color(float $phase): Color\Color
{
    $r = (int) ((Math\sin($phase) * 127) + 128);
    $g = (int) ((Math\sin($phase + 2.094) * 127) + 128);
    $b = (int) ((Math\sin($phase + 4.189) * 127) + 128);

    return Color\rgb($r, $g, $b);
}

final class BlocksState
{
    /** @var list<DragBlock> */
    public array $blocks;
    public null|int $dragging = null;
    public int $dragOffsetX = 0;
    public int $dragOffsetY = 0;
    public int $tick = 0;

    public function __construct()
    {
        $this->blocks = [
            new DragBlock(3, 2, 14, 7, Color\bright_red(), 'Red'),
            new DragBlock(20, 2, 14, 7, Color\bright_green(), 'Green'),
            new DragBlock(37, 2, 14, 7, Color\bright_blue(), 'Blue'),
            new DragBlock(3, 11, 14, 7, Color\bright_yellow(), 'Yellow'),
            new DragBlock(20, 11, 14, 7, Color\bright_magenta(), 'Magenta'),
            new DragBlock(37, 11, 14, 7, Color\bright_cyan(), 'Cyan'),
            new DragBlock(54, 2, 14, 7, Color\bright_white(), 'Rainbow', rainbow: true),
        ];
    }
}

Async\main(static function (): int {
    $app = Terminal\Application::create(
        new BlocksState(),
        title: 'Drag Blocks',
        tickInterval: DateTime\Duration::milliseconds(16),
        mouseMotion: true,
    );

    $app->on(Event\Key::class, static function (Event\Key $event) use ($app): void {
        if ($event->is('ctrl+c') || $event->is('escape')) {
            $app->stop();
        }
    });

    $app->on(Event\Mouse::class, static function (Event\Mouse $event, BlocksState $state) use ($app): void {
        $col = $event->column;
        $row = $event->row;

        if ($event->kind === Event\MouseKind::Press && $event->button === Event\MouseButton::Left) {
            for ($i = Iter\count($state->blocks) - 1; $i >= 0; $i--) {
                $block = $state->blocks[$i];
                if (!$block->contains($col, $row)) {
                    continue;
                }

                $state->dragOffsetX = $col - $block->x;
                $state->dragOffsetY = $row - $block->y;

                $picked = Vec\slice($state->blocks, $i, 1);
                $state->blocks[] = $picked[0];
                $state->dragging = Iter\count($state->blocks) - 1;
                break;
            }

            return;
        }

        if ($event->kind === Event\MouseKind::Release) {
            $state->dragging = null;
            return;
        }

        if ($event->kind === Event\MouseKind::Drag && $state->dragging !== null) {
            /** @var non-negative-int $idx */
            $idx = $state->dragging;
            $block = $state->blocks[$idx];
            $block->x = $col - $state->dragOffsetX;
            $block->y = $row - $state->dragOffsetY;
        }
    });

    return $app->run(static function (Terminal\Frame $frame, BlocksState $state): void {
        $buffer = $frame->buffer();
        $state->tick++;

        [$main, $statusBar] = Layout\vertical($frame, [
            Layout\fill(),
            Layout\fixed(1),
        ]);

        $phase = $state->tick * 0.05;

        foreach ($state->blocks as $i => $block) {
            $isDragging = $state->dragging === $i;
            $color = $block->rainbow ? namespace\rainbow_color($phase) : $block->color;

            $x = Math\clamp($block->x, 0, $main->right() - $block->width);
            $y = Math\clamp($block->y, $main->y, $main->bottom() - $block->height);

            $blockRect = new Terminal\Rect(
                Math\maxva(0, $x),
                Math\maxva(0, $y),
                Math\minva($block->width, $main->right() - Math\maxva(0, $x)),
                Math\minva($block->height, $main->bottom() - Math\maxva(0, $y)),
            );

            if ($blockRect->isEmpty()) {
                continue;
            }

            if ($block->rainbow && !$isDragging) {
                [$tl, $tr, $bl, $br, $h, $v] = Widget\BorderStyle::Rounded->characters();
                $w = $blockRect->width;
                $bh = $blockRect->height;
                $colorStep = 0.3;
                $pos = 0;

                for ($bx = 0; $bx < $w; $bx++) {
                    $char = match (true) {
                        $bx === 0 => $tl,
                        $bx === ($w - 1) => $tr,
                        default => $h,
                    };
                    $c = namespace\rainbow_color($phase + ($pos * $colorStep));
                    $buffer->set($blockRect->x + $bx, $blockRect->y, new Terminal\Cell($char, [Ansi\foreground($c)]));
                    $pos++;
                }

                for ($by = 1; $by < ($bh - 1); $by++) {
                    $c = namespace\rainbow_color($phase + ($pos * $colorStep));
                    $buffer->set($blockRect->right() - 1, $blockRect->y + $by, new Terminal\Cell($v, [Ansi\foreground(
                        $c,
                    )]));
                    $pos++;
                }

                for ($bx = $w - 1; $bx >= 0; $bx--) {
                    $char = match (true) {
                        $bx === ($w - 1) => $br,
                        $bx === 0 => $bl,
                        default => $h,
                    };
                    $c = namespace\rainbow_color($phase + ($pos * $colorStep));
                    $buffer->set(
                        $blockRect->x + $bx,
                        $blockRect->bottom() - 1,
                        new Terminal\Cell($char, [Ansi\foreground($c)]),
                    );
                    $pos++;
                }

                for ($by = $bh - 2; $by >= 1; $by--) {
                    $c = namespace\rainbow_color($phase + ($pos * $colorStep));
                    $buffer->set($blockRect->x, $blockRect->y + $by, new Terminal\Cell($v, [Ansi\foreground($c)]));
                    $pos++;
                }

                $titleChars = [' ', 'R', 'a', 'i', 'n', 'b', 'o', 'w', ' '];
                $titleLen = Iter\count($titleChars);
                $titleOffset = (int) (($w - $titleLen) / 2);
                if ($titleOffset < 1) {
                    $titleOffset = 1;
                }

                for ($ti = 0; $ti < $titleLen && ($titleOffset + $ti) < ($w - 1); $ti++) {
                    $c = namespace\rainbow_color($phase + (($titleOffset + $ti) * $colorStep));
                    $buffer->set(
                        $blockRect->x + $titleOffset + $ti,
                        $blockRect->y,
                        new Terminal\Cell($titleChars[$ti], [Ansi\foreground($c), Style\bold()]),
                    );
                }

                continue;
            }

            $borderStyle = $isDragging
                ? Widget\Border::double(Ansi\foreground(Color\bright_white()))
                : Widget\Border::rounded(Ansi\foreground($color));

            Widget\Block::new()
                ->title(" {$block->label} ", Widget\Alignment::Center)
                ->titleStyle(Ansi\foreground($isDragging ? Color\bright_white() : $color), Style\bold())
                ->border($borderStyle)
                ->background($color)
                ->render($blockRect, Widget\Paragraph::new([]), $buffer);
        }

        $hint = $state->dragging !== null
            ? ' Dragging... release to drop '
            : ' Click and drag blocks to move them | Esc: quit ';

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled($hint, Ansi\foreground(Color\bright_black())),
        ])])->render($statusBar, $buffer);
    });
});
