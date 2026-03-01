<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

/**
 * A horizontal tab bar widget.
 *
 * Renders tab titles in a single row: ` Tab1 │ Tab2 │ Tab3 `
 * The selected tab is rendered with the active style, others with the inactive style.
 */
final class Tabs implements WidgetInterface
{
    /** @var list<string> */
    private array $titles = [];

    private null|int $highlighted = null;
    private Style $activeStyle;
    private Style $inactiveStyle;

    private function __construct()
    {
        $this->activeStyle = new Style();
        $this->inactiveStyle = new Style();
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the tab titles.
     *
     * @param list<string> $titles
     */
    public function titles(array $titles): self
    {
        $this->titles = $titles;
        return $this;
    }

    /**
     * Set the selected (active) tab index.
     */
    public function highlight(int $index): self
    {
        $this->highlighted = $index;
        return $this;
    }

    /**
     * Set the style for the active tab.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function activeStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->activeStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    /**
     * Set the style for inactive tabs.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function inactiveStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->inactiveStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty() || $this->titles === []) {
            return;
        }

        $x = $area->x;
        $y = $area->y;

        foreach ($this->titles as $i => $title) {
            if ($x >= $area->right()) {
                break;
            }

            if ($i > 0) {
                if ($x < $area->right()) {
                    $buffer->set(
                        $x,
                        $y,
                        new Cell("\u{2502}", $this->inactiveStyle->foreground, $this->inactiveStyle->background, []),
                    );
                    $x++;
                }
            }

            $isActive = $i === $this->highlighted;
            $style = $isActive ? $this->activeStyle : $this->inactiveStyle;

            $text = ' ' . $title . ' ';
            $len = Str\width($text);

            /** @var non-negative-int $remaining */
            $remaining = $area->right() - $x;
            $buffer->setString(
                $x,
                $y,
                Str\width_slice($text, 0, $remaining),
                $style->foreground,
                $style->background,
                $style->modifiers,
            );

            $x += $len;
        }
    }
}
