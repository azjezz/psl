<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Math;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

use function array_values;
use function count;

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

    /** @var list<ControlSequenceIntroducer> */
    private array $headerStyle = [];

    /** @var list<ControlSequenceIntroducer> */
    private array $highlightStyle = [];

    private function __construct() {}

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
    public function headerStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->headerStyle = array_values($style);

        return $this;
    }

    /**
     * Set the style for the highlighted row.
     */
    public function highlightStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->highlightStyle = array_values($style);

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
                $buffer->setString($x, $currentY, $text, $this->headerStyle);
                $x += $colWidth;
                if ($x >= $area->right()) {
                    break;
                }
            }

            $currentY++;

            // Separator
            if ($currentY < $area->bottom()) {
                for ($x = $area->x; $x < $area->right(); $x++) {
                    $buffer->set($x, $currentY, new Cell("\u{2500}", $this->headerStyle));
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

            // Fill entire row with highlight style first
            if ($isHighlighted && $this->highlightStyle !== []) {
                for ($x = $area->x; $x < $area->right(); $x++) {
                    $buffer->set($x, $currentY, new Cell(' ', $this->highlightStyle));
                }
            }

            $x = $area->x;
            foreach ($row as $col => $span) {
                /** @var non-negative-int $colWidth */
                $colWidth = $this->widths[$col] ?? Str\width($span->content);
                $text = Str\pad_right(Str\width_slice($span->content, 0, $colWidth), $colWidth);

                $style = $isHighlighted && $this->highlightStyle !== []
                    ? [...$span->style, ...$this->highlightStyle]
                    : $span->style;

                $buffer->setString($x, $currentY, $text, $style);
                $x += $colWidth;
                if ($x >= $area->right()) {
                    break;
                }
            }

            $currentY++;
        }
    }
}
