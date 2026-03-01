<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Math;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

/**
 * A table widget that renders columnar data with headers, a separator, and optional row highlighting.
 */
final class Table implements WidgetInterface
{
    /** @var list<string> */
    private array $headers = [];

    /** @var list<non-negative-int> */
    private array $widths = [];

    /** @var list<list<Span>> */
    private array $rows = [];

    private null|int $highlightIndex = null;
    private int $scrollOffset = 0;

    private Style $headerStyle;
    private Style $highlightStyle;

    private function __construct()
    {
        $this->headerStyle = new Style();
        $this->highlightStyle = new Style();
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param list<string> $headers
     */
    public function headers(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Column widths in characters.
     *
     * @param list<non-negative-int> $widths
     */
    public function widths(array $widths): self
    {
        $this->widths = $widths;
        return $this;
    }

    /**
     * Set the data rows. Each row is a list of Spans (one per column).
     *
     * @param list<list<Span>> $rows
     */
    public function rows(array $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * Highlight a specific row index.
     */
    public function highlight(int $index): self
    {
        $this->highlightIndex = $index;
        return $this;
    }

    /**
     * Set the scroll offset (number of data rows to skip).
     */
    public function scroll(int $offset): self
    {
        $this->scrollOffset = Math\maxva(0, $offset);
        return $this;
    }

    /**
     * Set the style for the header row.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function headerStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->headerStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    /**
     * Set the style for the highlighted row.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function highlightStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->highlightStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $currentY = $area->y;

        // Headers + separator
        if ($this->headers !== [] && $currentY < $area->bottom()) {
            $x = $area->x;
            foreach ($this->headers as $col => $header) {
                /** @var non-negative-int $colWidth */
                $colWidth = $this->widths[$col] ?? Str\width($header);
                $text = Str\pad_right(Str\width_slice($header, 0, $colWidth), $colWidth);
                $buffer->setString(
                    $x,
                    $currentY,
                    $text,
                    $this->headerStyle->foreground,
                    $this->headerStyle->background,
                    $this->headerStyle->modifiers,
                );
                $x += $colWidth;
                if ($x >= $area->right()) {
                    break;
                }
            }

            $currentY++;

            // Separator
            if ($currentY < $area->bottom()) {
                for ($x = $area->x; $x < $area->right(); $x++) {
                    $buffer->set(
                        $x,
                        $currentY,
                        new Cell("\u{2500}", $this->headerStyle->foreground, $this->headerStyle->background, []),
                    );
                }

                $currentY++;
            }
        }

        $visibleHeight = $area->bottom() - $currentY;
        $maxScroll = Math\maxva(0, count($this->rows) - $visibleHeight);
        $scrollOffset = Math\minva($this->scrollOffset, $maxScroll);

        for ($rowIdx = $scrollOffset; $rowIdx < count($this->rows); $rowIdx++) {
            if ($currentY >= $area->bottom()) {
                break;
            }

            /** @var non-negative-int $rowIdx */
            $row = $this->rows[$rowIdx];
            $isHighlighted = $rowIdx === $this->highlightIndex;

            // Fill entire row with highlight background first
            if ($isHighlighted && $this->highlightStyle->background !== null) {
                for ($x = $area->x; $x < $area->right(); $x++) {
                    $buffer->set(
                        $x,
                        $currentY,
                        new Cell(
                            ' ',
                            $this->highlightStyle->foreground,
                            $this->highlightStyle->background,
                            $this->highlightStyle->modifiers,
                        ),
                    );
                }
            }

            $x = $area->x;
            foreach ($row as $col => $span) {
                /** @var non-negative-int $colWidth */
                $colWidth = $this->widths[$col] ?? Str\width($span->content);
                $text = Str\pad_right(Str\width_slice($span->content, 0, $colWidth), $colWidth);

                $fg = $span->foreground;
                $bg = $span->background;
                $mods = $span->modifiers;
                if ($isHighlighted) {
                    $fg = $this->highlightStyle->foreground ?? $fg;
                    $bg = $this->highlightStyle->background ?? $bg;
                    $mods = $this->highlightStyle->modifiers !== [] ? $this->highlightStyle->modifiers : $mods;
                }

                $buffer->setString($x, $currentY, $text, $fg, $bg, $mods);
                $x += $colWidth;
                if ($x >= $area->right()) {
                    break;
                }
            }

            $currentY++;
        }
    }
}
