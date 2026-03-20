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
 * A selectable menu widget.
 */
final class Menu implements WidgetInterface
{
    /**
     * @var list<MenuItem>
     */
    private array $items;

    private null|int $highlighted = null;
    private int $scrollOffset = 0;

    /** @var list<ControlSequenceIntroducer> */
    private array $highlightStyle = [];

    /**
     * @param list<MenuItem> $items
     */
    private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param list<MenuItem> $items
     */
    public static function new(array $items): self
    {
        return new self($items);
    }

    /**
     * Set the highlighted (selected) item index.
     */
    public function highlight(int $index): self
    {
        $this->highlighted = $index;
        return $this;
    }

    /**
     * Set the scroll offset (number of items to skip).
     */
    public function scroll(int $offset): self
    {
        $this->scrollOffset = Math\maxva(0, $offset);
        return $this;
    }

    /**
     * Set the style for the highlighted item.
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

        $itemCount = count($this->items);
        $maxScroll = Math\maxva(0, $itemCount - $area->height);
        $scrollOffset = Math\minva($this->scrollOffset, $maxScroll);

        $row = 0;
        for ($i = $scrollOffset; $i < $itemCount; $i++) {
            $y = $area->y + $row;
            if ($y >= $area->bottom()) {
                break;
            }

            /** @var non-negative-int $i */
            $item = $this->items[$i];
            $isHighlighted = $this->highlighted === $i;

            $x = $area->x;
            foreach ($item->spans as $span) {
                $chars = Str\chunk($span->content);
                foreach ($chars as $char) {
                    if ($x >= $area->right()) {
                        break 2;
                    }

                    $charWidth = Str\width($char);
                    $style = $isHighlighted && $this->highlightStyle !== []
                        ? [...$span->style, ...$this->highlightStyle]
                        : $span->style;

                    $buffer->set($x, $y, new Cell($char, $style));
                    for ($w = 1; $w < $charWidth && ($x + $w) < $area->right(); $w++) {
                        $buffer->set($x + $w, $y, new Cell('', $style));
                    }

                    $x += $charWidth;
                }
            }

            if ($isHighlighted && $this->highlightStyle !== []) {
                while ($x < $area->right()) {
                    $buffer->set($x, $y, new Cell(' ', $this->highlightStyle));
                    $x++;
                }
            }

            $row++;
        }
    }
}
