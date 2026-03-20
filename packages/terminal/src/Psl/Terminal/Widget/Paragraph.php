<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Math;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

use function array_slice;
use function count;

/**
 * A paragraph widget that renders lines of styled text with wrapping, scrolling, and alignment.
 */
final class Paragraph implements WidgetInterface
{
    /**
     * @var list<Line>
     */
    private array $lines;

    private Wrap $wrap = Wrap::None;
    private int $scrollOffset = 0;
    private Alignment $alignment = Alignment::Left;

    /**
     * @param list<Line> $lines
     */
    private function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    /**
     * @param list<Line> $lines
     */
    public static function new(array $lines): self
    {
        return new self($lines);
    }

    public function wrap(Wrap $wrap): self
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function scroll(int $offset): self
    {
        $this->scrollOffset = Math\maxva(0, $offset);
        return $this;
    }

    public function alignment(Alignment $alignment): self
    {
        $this->alignment = $alignment;
        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $wrappedLines = Internal\LineWrapper::wrap($this->lines, $this->wrap, $area->width);

        $maxScroll = Math\maxva(0, count($wrappedLines) - $area->height);
        $scrollOffset = Math\minva($this->scrollOffset, $maxScroll);
        $visibleLines = array_slice($wrappedLines, $scrollOffset, $area->height);

        foreach ($visibleLines as $lineIndex => $line) {
            $y = $area->y + $lineIndex;
            if ($y >= $area->bottom()) {
                break;
            }

            $this->renderLine($line, $y, $area, $buffer);
        }
    }

    private function renderLine(Line $line, int $y, Rect $area, Buffer $buffer): void
    {
        $lineWidth = $line->width();
        $startX = match ($this->alignment) {
            Alignment::Left => $area->x,
            Alignment::Right => $area->x + Math\maxva(0, $area->width - $lineWidth),
            Alignment::Center => $area->x + (int) (Math\maxva(0, $area->width - $lineWidth) / 2),
        };

        $x = $startX;
        foreach ($line->spans as $span) {
            $chars = Str\chunk($span->content);
            foreach ($chars as $char) {
                if ($x >= $area->right()) {
                    break 2;
                }

                $charWidth = Str\width($char);
                if ($x >= $area->x) {
                    $buffer->set($x, $y, new Cell($char, $span->style));
                    for ($w = 1; $w < $charWidth && ($x + $w) < $area->right(); $w++) {
                        $buffer->set($x + $w, $y, new Cell('', $span->style));
                    }
                }

                $x += $charWidth;
            }
        }
    }
}
