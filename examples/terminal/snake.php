<?php

declare(strict_types=1);

namespace Psl\Example\Terminal;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\DateTime;
use Psl\Math;
use Psl\PseudoRandom;
use Psl\Str;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;

use function array_pop;

require __DIR__ . '/../../vendor/autoload.php';

enum Direction
{
    case Up;
    case Down;
    case Left;
    case Right;
}

final class SnakeState
{
    /** @var list<array{int, int}> Snake body segments — head at index 0 */
    public array $snake = [[5, 5], [4, 5], [3, 5]];
    public Direction $direction = Direction::Right;
    public Direction $nextDirection = Direction::Right;
    /** @var array{int, int} */
    public array $food = [10, 10];
    public int $score = 0;
    public int $highScore = 0;
    public bool $gameOver = false;
    public bool $paused = false;
}

/**
 * Check if two directions are opposite (180° reversal).
 */
function is_opposite(Direction $a, Direction $b): bool
{
    return match ($a) {
        Direction::Up => $b === Direction::Down,
        Direction::Down => $b === Direction::Up,
        Direction::Left => $b === Direction::Right,
        Direction::Right => $b === Direction::Left,
    };
}

/**
 * Get the (dx, dy) offset for a direction.
 *
 * @return array{int, int}
 */
function direction_offset(Direction $direction): array
{
    return match ($direction) {
        Direction::Up => [0, -1],
        Direction::Down => [0, 1],
        Direction::Left => [-1, 0],
        Direction::Right => [1, 0],
    };
}

/**
 * Spawn food at a random position inside the playable area that doesn't overlap the snake.
 *
 * @param list<array{int, int}> $snake
 *
 * @return array{int, int}
 */
function spawn_food(Terminal\Rect $area, array $snake): array
{
    if ($area->width <= 0 || $area->height <= 0) {
        return [0, 0];
    }

    $maxAttempts = $area->width * $area->height;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $x = PseudoRandom\int($area->x, $area->x + $area->width - 1);
        $y = PseudoRandom\int($area->y, $area->y + $area->height - 1);

        $overlaps = false;
        foreach ($snake as $segment) {
            if (!($segment[0] === $x && $segment[1] === $y)) {
                continue;
            }

            $overlaps = true;
            break;
        }

        if (!$overlaps) {
            return [$x, $y];
        }
    }

    return [$area->x, $area->y];
}

/**
 * Reset the snake to its initial state for a new game.
 */
function reset_game(SnakeState $state, Terminal\Rect $area): void
{
    $centerX = $area->x + (int) ($area->width / 2);
    $centerY = $area->y + (int) ($area->height / 2);

    $state->snake = [[$centerX, $centerY], [$centerX - 1, $centerY], [$centerX - 2, $centerY]];
    $state->direction = Direction::Right;
    $state->nextDirection = Direction::Right;
    $state->score = 0;
    $state->gameOver = false;
    $state->paused = false;
    $state->food = namespace\spawn_food($area, $state->snake);
}

Async\main(static function (): int {
    $app = Terminal\Application::create::<SnakeState>(
        new SnakeState(),
        title: 'Snake',
        tickInterval: DateTime\Duration::milliseconds(8),
    );

    /** @var array{Terminal\Rect}|null $gameAreaRef */
    $gameAreaRef = null;

    $app->interval(DateTime\Duration::milliseconds(100), static function (SnakeState $state) use (&$gameAreaRef): void {
        if ($state->paused || $state->gameOver) {
            return;
        }

        if (!namespace\is_opposite($state->nextDirection, $state->direction)) {
            $state->direction = $state->nextDirection;
        }

        [$dx, $dy] = namespace\direction_offset($state->direction);
        $head = $state->snake[0];
        $newHead = [$head[0] + $dx, $head[1] + $dy];

        if ($gameAreaRef !== null) {
            $area = $gameAreaRef[0];
            if (
                $newHead[0] < $area->x
                || $newHead[0] >= $area->right()
                || $newHead[1] < $area->y
                || $newHead[1] >= $area->bottom()
            ) {
                $state->gameOver = true;
                $state->highScore = Math\maxva::<int>($state->highScore, $state->score);
                return;
            }
        }

        foreach ($state->snake as $segment) {
            if (!($segment[0] === $newHead[0] && $segment[1] === $newHead[1])) {
                continue;
            }

            $state->gameOver = true;
            $state->highScore = Math\maxva::<int>($state->highScore, $state->score);
            return;
        }

        /** @var list<array{int, int}> $newSnake */
        $newSnake = [$newHead];
        foreach ($state->snake as $segment) {
            $newSnake[] = $segment;
        }

        if ($newHead[0] === $state->food[0] && $newHead[1] === $state->food[1]) {
            $state->score++;
            $state->snake = $newSnake;

            if ($gameAreaRef !== null) {
                $state->food = namespace\spawn_food($gameAreaRef[0], $state->snake);
            }
        } else {
            array_pop($newSnake);
            $state->snake = $newSnake;
        }
    });

    $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $event, SnakeState $state) use ($app, &$gameAreaRef): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
            return;
        }

        if (($event->char === 'r' || $event->char === 'R') && $state->gameOver) {
            if ($gameAreaRef !== null) {
                namespace\reset_game($state, $gameAreaRef[0]);
            }

            return;
        }

        if (($event->char === 'p' || $event->char === 'P') && !$state->gameOver) {
            $state->paused = !$state->paused;
            return;
        }

        $newDirection = match (true) {
            $event->is('up'), $event->char === 'w', $event->char === 'W' => Direction::Up,
            $event->is('down'), $event->char === 's', $event->char === 'S' => Direction::Down,
            $event->is('left'), $event->char === 'a', $event->char === 'A' => Direction::Left,
            $event->is('right'), $event->char === 'd', $event->char === 'D' => Direction::Right,
            default => null,
        };

        if ($newDirection !== null && !$state->paused && !$state->gameOver) {
            $state->nextDirection = $newDirection;
        }
    });

    return $app->run(static function (Terminal\Frame $frame, SnakeState $state) use (&$gameAreaRef): void {
        $buffer = $frame->buffer();

        [$gameSection, $statusBar] = Layout\vertical($frame, [
            Layout\fill(),
            Layout\fixed(1),
        ]);

        $borderColor = $state->gameOver ? Color\bright_red() : Color\bright_green();
        $gameBlock = Widget\Block::new()
            ->title(' Snake ')
            ->titleStyle(Ansi\foreground(Color\bright_green()), Style\bold())
            ->border(Widget\Border::rounded(Ansi\foreground($borderColor)));

        $gameBlock->render($gameSection, Widget\Paragraph::new([]), $buffer);
        $innerArea = $gameBlock->innerArea($gameSection);

        $gameAreaRef = [$innerArea];

        if ($innerArea->contains($state->food[0], $state->food[1])) {
            $buffer->set($state->food[0], $state->food[1], new Terminal\Cell('◆', [Ansi\foreground(
                Color\bright_red(),
            )]));
        }

        foreach ($state->snake as $i => $segment) {
            if (!$innerArea->contains($segment[0], $segment[1])) {
                continue;
            }

            if ($i === 0) {
                $buffer->set($segment[0], $segment[1], new Terminal\Cell('●', [Ansi\foreground(Color\bright_green())]));
            } else {
                $buffer->set($segment[0], $segment[1], new Terminal\Cell('●', [Ansi\foreground(Color\green())]));
            }
        }

        if ($state->gameOver) {
            $text = ' GAME OVER - Press R to restart ';
            $textLen = Str\width($text);
            $textX = $innerArea->x + (int) (($innerArea->width - $textLen) / 2);
            $textY = $innerArea->y + (int) ($innerArea->height / 2);
            $buffer->setString($textX, $textY, $text, [
                Ansi\foreground(Color\bright_white()),
                Ansi\background(Color\bright_red()),
            ]);
        } elseif ($state->paused) {
            $text = ' PAUSED ';
            $textLen = Str\width($text);
            $textX = $innerArea->x + (int) (($innerArea->width - $textLen) / 2);
            $textY = $innerArea->y + (int) ($innerArea->height / 2);
            $buffer->setString($textX, $textY, $text, [
                Ansi\foreground(Color\bright_white()),
                Ansi\background(Color\bright_yellow()),
            ]);
        }

        $scoreText = Str\format(' Score: %d  High: %d', $state->score, $state->highScore);
        $controlsText = "\u{2191}/\u{2193}/\u{2190}/\u{2192} or WASD: move | p: pause | r: restart | Ctrl+C: quit ";
        $controlsLen = Str\width($controlsText);

        [$statusLeft, $statusRight] = Layout\horizontal($statusBar, [
            Layout\fill(),
            Layout\fixed($controlsLen),
        ]);

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled($scoreText, Ansi\foreground(Color\bright_cyan()), Style\bold()),
        ])])->render($statusLeft, $buffer);

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled($controlsText, Ansi\foreground(Color\bright_black())),
        ])])->alignment(Widget\Alignment::Right)->render($statusRight, $buffer);
    });
});
