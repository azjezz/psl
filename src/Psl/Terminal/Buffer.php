<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Psl\Ansi;
use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\IO;
use Psl\Str;

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
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function setString(
        int $x,
        int $y,
        string $text,
        null|Color $fg = null,
        null|Color $bg = null,
        array $modifiers = [],
    ): void {
        if ($y < 0 || $y >= $this->height) {
            return;
        }

        $codepoints = Str\length($text);
        $col = $x;
        for ($i = 0; $i < $codepoints && $col < $this->width; $i++) {
            $char = Str\slice($text, $i, 1);
            $charWidth = Str\width($char);
            if ($col >= 0) {
                $this->cells[$y][$col] = new Cell($char, $fg, $bg, $modifiers);
                for ($w = 1; $w < $charWidth && ($col + $w) < $this->width; $w++) {
                    /** @var non-negative-int $wideCol */
                    $wideCol = $col + $w;
                    $this->cells[$y][$wideCol] = new Cell('', $fg, $bg, $modifiers);
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
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $this->cells[$y][$x] = $cell;
            }
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
     */
    public function flush(IO\WriteHandleInterface $output): void
    {
        $buf = '';

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $cell = $this->cells[$y][$x];
                $prev = $this->previous[$y][$x] ?? null;

                if ($prev !== null && $cell->equals($prev)) {
                    continue;
                }

                $buf .= Ansi\Cursor\move_to($y + 1, $x + 1)->toString();
                $sequences = [];
                if ($cell->foreground !== null) {
                    $sequences[] = Ansi\foreground($cell->foreground);
                }

                if ($cell->background !== null) {
                    $sequences[] = Ansi\background($cell->background);
                }

                foreach ($cell->modifiers as $modifier) {
                    $sequences[] = $modifier;
                }

                $buf .= $sequences !== [] ? Ansi\apply($cell->grapheme, ...$sequences) : $cell->grapheme;
            }
        }

        $this->previous = self::copyGrid($this->cells, $this->width, $this->height);

        if ($buf !== '') {
            $output->writeAll($buf);
        }
    }

    /**
     * @return array<non-negative-int, array<non-negative-int, Cell>>
     */
    private static function createGrid(int $width, int $height): array
    {
        $empty = new Cell();
        /** @var array<non-negative-int, array<non-negative-int, Cell>> $grid */
        $grid = [];
        for ($y = 0; $y < $height; $y++) {
            /** @var array<non-negative-int, Cell> $row */
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $row[] = $empty;
            }

            $grid[] = $row;
        }

        return $grid;
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
            /** @var array<non-negative-int, Cell> $row */
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $row[] = $source[$y][$x];
            }

            $copy[] = $row;
        }

        return $copy;
    }
}
