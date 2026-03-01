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
    private Style $style;
    private Style $cursorStyle;
    private Style $placeholderStyle;

    private function __construct()
    {
        $this->style = new Style();
        $this->cursorStyle = new Style();
        $this->placeholderStyle = new Style();
    }

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
        $this->cursor = Math\maxva(0, $cursor);
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
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function style(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->style = new Style($foreground, $background, $modifiers);

        return $this;
    }

    /**
     * Set the style for the cursor character.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function cursorStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->cursorStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    /**
     * Set the style for the placeholder text.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function placeholderStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->placeholderStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $width = $area->width;
        $y = $area->y;

        // Placeholder mode
        if ($this->value === '' && $this->placeholder !== '') {
            /** @var non-negative-int $width */
            $text = Str\width_slice($this->placeholder, 0, $width);
            $buffer->setString(
                $area->x,
                $y,
                $text,
                $this->placeholderStyle->foreground,
                $this->placeholderStyle->background,
                $this->placeholderStyle->modifiers,
            );

            // Show cursor at position 0
            $buffer->set(
                $area->x,
                $y,
                new Cell(
                    $this->placeholder !== '' ? Str\slice($this->placeholder, 0, 1) : "\u{2588}",
                    $this->cursorStyle->foreground,
                    $this->cursorStyle->background,
                    $this->cursorStyle->modifiers,
                ),
            );

            return;
        }

        $valueLen = Str\length($this->value);
        $cursor = Math\clamp($this->cursor, 0, $valueLen);

        /** @var non-negative-int $scrollOffset */
        $scrollOffset = $cursor >= $width ? Math\maxva(0, $cursor - $width + 1) : 0;

        /** @var non-negative-int $width */
        $visibleText = Str\width_slice($this->value, $scrollOffset, $width);
        $buffer->setString(
            $area->x,
            $y,
            $visibleText,
            $this->style->foreground,
            $this->style->background,
            $this->style->modifiers,
        );

        $cursorX = $area->x + ($cursor - $scrollOffset);
        if ($cursorX < $area->right()) {
            $cursorChar = $cursor < $valueLen ? Str\slice($this->value, $cursor, 1) : "\u{2588}";

            $buffer->set(
                $cursorX,
                $y,
                new Cell(
                    $cursorChar,
                    $this->cursorStyle->foreground,
                    $this->cursorStyle->background,
                    $this->cursorStyle->modifiers,
                ),
            );
        }
    }
}
