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
    private Style $highlightStyle;

    /**
     * @param list<MenuItem> $items
     */
    private function __construct(array $items)
    {
        $this->items = $items;
        $this->highlightStyle = new Style();
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
                $len = Str\length($span->content);
                for ($j = 0; $j < $len; $j++) {
                    if ($x >= $area->right()) {
                        break 2;
                    }

                    $char = Str\slice($span->content, $j, 1);
                    $charWidth = Str\width($char);
                    $fg = $span->foreground;
                    $bg = $span->background;
                    $mods = $span->modifiers;
                    if ($isHighlighted) {
                        $fg = $this->highlightStyle->foreground ?? $fg;
                        $bg = $this->highlightStyle->background ?? $bg;
                        $mods = $this->highlightStyle->modifiers !== [] ? $this->highlightStyle->modifiers : $mods;
                    }

                    $buffer->set($x, $y, new Cell($char, $fg, $bg, $mods));
                    for ($w = 1; $w < $charWidth && ($x + $w) < $area->right(); $w++) {
                        $buffer->set($x + $w, $y, new Cell('', $fg, $bg, $mods));
                    }

                    $x += $charWidth;
                }
            }

            if ($isHighlighted && $this->highlightStyle->background !== null) {
                while ($x < $area->right()) {
                    $buffer->set(
                        $x,
                        $y,
                        new Cell(
                            ' ',
                            $this->highlightStyle->foreground,
                            $this->highlightStyle->background,
                            $this->highlightStyle->modifiers,
                        ),
                    );

                    $x++;
                }
            }

            $row++;
        }
    }
}
