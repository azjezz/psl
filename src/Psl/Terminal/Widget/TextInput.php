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
use function max;
use function mb_strlen;
use function mb_substr;

/**
 * A single-line text input widget with cursor visualization and placeholder support.
 *
 * The widget renders the current value with a visible cursor position.
 * When the text is longer than the available width, it scrolls to keep the cursor visible.
 */
final class TextInput implements WidgetInterface
{
    private string $value = '';
    private int $cursor = 0;
    private string $placeholder = '';

    /** @var list<ControlSequenceIntroducer> */
    private array $style = [];

    /** @var list<ControlSequenceIntroducer> */
    private array $cursorStyle = [];

    /** @var list<ControlSequenceIntroducer> */
    private array $placeholderStyle = [];

    private function __construct() {}

    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the current text value.
     */
    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set the cursor position (character index).
     */
    public function cursor(int $cursor): self
    {
        $this->cursor = max(0, $cursor);
        return $this;
    }

    /**
     * Set the placeholder text shown when value is empty.
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Set the style for the text.
     */
    public function style(ControlSequenceIntroducer ...$style): self
    {
        $this->style = array_values($style);

        return $this;
    }

    /**
     * Set the style for the cursor character.
     */
    public function cursorStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->cursorStyle = array_values($style);

        return $this;
    }

    /**
     * Set the style for the placeholder text.
     */
    public function placeholderStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->placeholderStyle = array_values($style);

        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $width = $area->width;
        $y = $area->y;

        if ($this->value === '' && $this->placeholder !== '') {
            /** @var non-negative-int $width */
            $text = Str\width_slice($this->placeholder, 0, $width);
            $buffer->setString($area->x, $y, $text, $this->placeholderStyle);

            $buffer->set($area->x, $y, new Cell(mb_substr($this->placeholder, 0, 1), $this->cursorStyle));

            return;
        }

        $valueLen = mb_strlen($this->value);
        /** @var non-negative-int $cursor */
        $cursor = Math\clamp($this->cursor, 0, $valueLen);

        $widthToCursor = Str\width(mb_substr($this->value, 0, $cursor));
        $scrollOffset = 0;
        if ($widthToCursor >= $width) {
            for ($offset = 1; $offset <= $cursor; $offset++) {
                /** @var non-negative-int $len */
                $len = $cursor - $offset;
                if (Str\width(mb_substr($this->value, $offset, $len)) < $width) {
                    $scrollOffset = $offset;
                    break;
                }
            }
        }

        /** @var non-negative-int $width */
        $visibleText = Str\width_slice($this->value, $scrollOffset, $width);
        $buffer->setString($area->x, $y, $visibleText, $this->style);

        /** @var non-negative-int $beforeLen */
        $beforeLen = $cursor - $scrollOffset;
        $cursorX = $area->x + Str\width(mb_substr($this->value, $scrollOffset, $beforeLen));
        if ($cursorX < $area->right()) {
            $cursorChar = $cursor < $valueLen ? mb_substr($this->value, $cursor, 1) : "\u{2588}";

            $buffer->set($cursorX, $y, new Cell($cursorChar, $this->cursorStyle));
        }
    }
}
