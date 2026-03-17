<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

use function array_values;

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

    /** @var list<ControlSequenceIntroducer> */
    private array $activeStyle = [];

    /** @var list<ControlSequenceIntroducer> */
    private array $inactiveStyle = [];

    private function __construct() {}

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
    public function activeStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->activeStyle = array_values($style);

        return $this;
    }

    /**
     * Set the style for inactive tabs.
     */
    public function inactiveStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->inactiveStyle = array_values($style);

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
                    $buffer->set($x, $y, new Cell("\u{2502}", $this->inactiveStyle));
                    $x++;
                }
            }

            $isActive = $i === $this->highlighted;
            $style = $isActive ? $this->activeStyle : $this->inactiveStyle;

            $text = ' ' . $title . ' ';
            $len = Str\width($text);

            /** @var non-negative-int $remaining */
            $remaining = $area->right() - $x;
            $buffer->setString($x, $y, Str\width_slice($text, 0, $remaining), $style);

            $x += $len;
        }
    }
}
