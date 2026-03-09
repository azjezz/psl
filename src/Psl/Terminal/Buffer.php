<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Psl\Ansi;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\Screen;
use Psl\IO;
use Psl\Str;

use function array_fill;

/**
 * A 2D grid of {@see Cell} objects representing the terminal screen.
 *
 * Supports diff-based rendering: only cells that changed since the last flush are written to output.
 */
final class Buffer
{
    /**
     * @var array<non-negative-int, array<non-negative-int, Cell>>
     */
    private array $cells;

    /**
     * @var array<non-negative-int, array<non-negative-int, Cell>>|null
     */
    private null|array $previous = null;

    public function __construct(
        private int $width,
        private int $height,
    ) {
        $this->cells = self::createGrid($width, $height);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get the cell at the given position, or null if out of bounds.
     */
    public function get(int $x, int $y): null|Cell
    {
        if ($x < 0 || $y < 0) {
            return null;
        }

        return $this->cells[$y][$x] ?? null;
    }

    /**
     * Set the cell at the given position.
     */
    public function set(int $x, int $y, Cell $cell): void
    {
        if ($x >= 0 && $x < $this->width && $y >= 0 && $y < $this->height) {
            $this->cells[$y][$x] = $cell;
        }
    }

    /**
     * Write a styled string into the buffer starting at (x, y).
     *
     * @param list<ControlSequenceIntroducer> $style
     */
    public function setString(int $x, int $y, string $text, array $style = []): void
    {
        if ($y < 0 || $y >= $this->height) {
            return;
        }

        $chars = Str\chunk($text);
        $col = $x;
        foreach ($chars as $char) {
            if ($col >= $this->width) {
                break;
            }

            $charWidth = Str\width($char);
            if ($col >= 0) {
                $this->cells[$y][$col] = new Cell($char, $style);
                for ($w = 1; $w < $charWidth && ($col + $w) < $this->width; $w++) {
                    /** @var non-negative-int $wideCol */
                    $wideCol = $col + $w;
                    $this->cells[$y][$wideCol] = new Cell('', $style);
                }
            }

            $col += $charWidth;
        }
    }

    /**
     * Fill the entire buffer with the given cell.
     */
    public function fill(Cell $cell): void
    {
        /** @var array<non-negative-int, Cell> $row */
        $row = array_fill(0, $this->width, $cell);
        for ($y = 0; $y < $this->height; $y++) {
            $this->cells[$y] = $row;
        }
    }

    /**
     * Clear the buffer (fill with empty cells).
     */
    public function clear(): void
    {
        $this->fill(new Cell());
    }

    /**
     * Resize the buffer to new dimensions, clearing all content.
     */
    public function resize(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;
        $this->cells = self::createGrid($width, $height);
        $this->previous = null;
    }

    /**
     * Flush the buffer using diff-based rendering.
     *
     * Only cells that changed since the last flush are written.
     * Consecutive cells skip cursor repositioning, and SGR sequences
     * are only emitted when the style changes.
     */
    public function flush(IO\WriteHandleInterface $output): void
    {
        $buf = Screen\set_mode(Screen\ScreenMode::SynchronizedOutput)->toString();
        $lastX = -2;
        $lastY = -1;
        /** @var list<ControlSequenceIntroducer> $lastStyle */
        $lastStyle = [];
        $sgrActive = false;

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                /** @var non-negative-int $x */
                $cell = $this->cells[$y][$x];
                $prev = $this->previous[$y][$x] ?? null;

                if ($prev !== null && $cell->equals($prev)) {
                    continue;
                }

                if ($y !== $lastY || $x !== ($lastX + 1)) {
                    if ($sgrActive) {
                        $buf .= "\e[0m";
                        $sgrActive = false;
                        $lastStyle = [];
                    }

                    /** @var positive-int $row */
                    $row = $y + 1;
                    /** @var positive-int $col */
                    $col = $x + 1;
                    $buf .= Ansi\Cursor\move_to($row, $col)->toString();
                }

                $styleChanged = !Cell::styleEqual($cell->style, $lastStyle);

                if ($styleChanged) {
                    if ($sgrActive) {
                        $buf .= "\e[0m";
                    }

                    if ($cell->style !== []) {
                        $buf .= Ansi\apply($cell->grapheme, ...$cell->style);
                        $sgrActive = true;
                    } else {
                        $buf .= $cell->grapheme;
                        $sgrActive = false;
                    }

                    $lastStyle = $cell->style;
                } elseif ($sgrActive) {
                    $buf .= Ansi\apply($cell->grapheme, ...$cell->style);
                } else {
                    $buf .= $cell->grapheme;
                }

                $lastX = $x;
                $lastY = $y;
            }
        }

        if ($sgrActive) {
            $buf .= "\e[0m";
        }

        $buf .= Screen\reset_mode(Screen\ScreenMode::SynchronizedOutput)->toString();

        $this->previous = self::copyGrid($this->cells, $this->width, $this->height);

        $output->writeAll($buf);
    }

    /**
     * @return array<non-negative-int, array<non-negative-int, Cell>>
     */
    private static function createGrid(int $width, int $height): array
    {
        $empty = new Cell();
        /** @var array<non-negative-int, Cell> $row */
        $row = array_fill(0, $width, $empty);

        /** @var array<non-negative-int, array<non-negative-int, Cell>> */
        return array_fill(0, $height, $row);
    }

    /**
     * @param array<non-negative-int, array<non-negative-int, Cell>> $source
     *
     * @return array<non-negative-int, array<non-negative-int, Cell>>
     */
    private static function copyGrid(array $source, int $width, int $height): array
    {
        /** @var array<non-negative-int, array<non-negative-int, Cell>> $copy */
        $copy = [];
        for ($y = 0; $y < $height; $y++) {
            $copy[] = $source[$y];
        }

        return $copy;
    }
}
