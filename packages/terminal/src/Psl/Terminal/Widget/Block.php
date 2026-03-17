<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi;
use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Str;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

use function array_values;

/**
 * A block widget that draws a border and optional title around an inner widget.
 */
final class Block
{
    private null|string $title = null;

    /** @var list<ControlSequenceIntroducer> */
    private array $titleStyle = [];
    private Alignment $titleAlignment = Alignment::Left;
    private null|Border $border = null;
    private Padding $padding;
    private Padding $margin;
    private null|ControlSequenceIntroducer $background = null;

    private function __construct()
    {
        $this->padding = new Padding();
        $this->margin = new Padding();
    }

    public static function new(): self
    {
        return new self();
    }

    public function title(string $title, Alignment $alignment = Alignment::Left): self
    {
        $this->title = $title;
        $this->titleAlignment = $alignment;
        return $this;
    }

    public function titleStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->titleStyle = array_values($style);

        return $this;
    }

    public function border(null|Border $border): self
    {
        $this->border = $border;
        return $this;
    }

    /**
     * @param non-negative-int $top
     * @param non-negative-int $right
     * @param non-negative-int $bottom
     * @param non-negative-int $left
     */
    public function padding(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self
    {
        $this->padding = new Padding($top, $right, $bottom, $left);
        return $this;
    }

    /**
     * @param non-negative-int $top
     * @param non-negative-int $right
     * @param non-negative-int $bottom
     * @param non-negative-int $left
     */
    public function margin(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self
    {
        $this->margin = new Padding($top, $right, $bottom, $left);
        return $this;
    }

    public function background(null|Color $background): self
    {
        $this->background = $background !== null ? Ansi\background($background) : null;
        return $this;
    }

    /**
     * Render the block with an inner widget.
     */
    public function render(Rect $area, WidgetInterface $inner, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $borderArea = $area->inner(
            top: $this->margin->top,
            right: $this->margin->right,
            bottom: $this->margin->bottom,
            left: $this->margin->left,
        );

        if ($borderArea->isEmpty()) {
            return;
        }

        $this->renderBorder($borderArea, $buffer);

        $innerArea = $this->innerArea($area);
        if ($innerArea->isEmpty()) {
            return;
        }

        $inner->render($innerArea, $buffer);

        $this->fillBackground($area, $buffer);
    }

    /**
     * Calculate the inner area (area minus margin, border, and padding).
     */
    public function innerArea(Rect $area): Rect
    {
        return $area->inner(
            top: $this->margin->top + ($this->border?->top ? 1 : 0) + $this->padding->top,
            right: $this->margin->right + ($this->border?->right ? 1 : 0) + $this->padding->right,
            bottom: $this->margin->bottom + ($this->border?->bottom ? 1 : 0) + $this->padding->bottom,
            left: $this->margin->left + ($this->border?->left ? 1 : 0) + $this->padding->left,
        );
    }

    private function fillBackground(Rect $area, Buffer $buffer): void
    {
        if ($this->background === null) {
            return;
        }

        $bgArea = $area->inner(
            top: $this->margin->top + ($this->border?->top ? 1 : 0),
            right: $this->margin->right + ($this->border?->right ? 1 : 0),
            bottom: $this->margin->bottom + ($this->border?->bottom ? 1 : 0),
            left: $this->margin->left + ($this->border?->left ? 1 : 0),
        );

        $bgCsi = $this->background;
        for ($y = $bgArea->y; $y < $bgArea->bottom(); $y++) {
            for ($x = $bgArea->x; $x < $bgArea->right(); $x++) {
                $cell = $buffer->get($x, $y);
                if ($cell !== null) {
                    $buffer->set($x, $y, new Cell($cell->grapheme, [$bgCsi, ...$cell->style]));
                }
            }
        }
    }

    private function renderBorder(Rect $area, Buffer $buffer): void
    {
        if ($this->border === null) {
            return;
        }

        $border = $this->border;
        [$tl, $tr, $bl, $br, $h, $v] = $border->characters();
        $style = $border->style;

        // Top row
        self::renderCorner($buffer, $area->x, $area->y, $border->top, $border->left, $tl, $h, $v, $style);

        if ($border->top) {
            for ($x = $area->x + 1; $x < ($area->right() - 1); $x++) {
                $buffer->set($x, $area->y, new Cell($h, $style));
            }
        }

        if ($area->width > 1) {
            self::renderCorner(
                $buffer,
                $area->right() - 1,
                $area->y,
                $border->top,
                $border->right,
                $tr,
                $h,
                $v,
                $style,
            );
        }

        for ($y = $area->y + 1; $y < ($area->bottom() - 1); $y++) {
            if ($border->left) {
                $buffer->set($area->x, $y, new Cell($v, $style));
            }

            if ($border->right && $area->width > 1) {
                $buffer->set($area->right() - 1, $y, new Cell($v, $style));
            }
        }

        if ($area->height > 1) {
            self::renderCorner(
                $buffer,
                $area->x,
                $area->bottom() - 1,
                $border->bottom,
                $border->left,
                $bl,
                $h,
                $v,
                $style,
            );

            if ($border->bottom) {
                for ($x = $area->x + 1; $x < ($area->right() - 1); $x++) {
                    $buffer->set($x, $area->bottom() - 1, new Cell($h, $style));
                }
            }

            if ($area->width > 1) {
                self::renderCorner(
                    $buffer,
                    $area->right() - 1,
                    $area->bottom() - 1,
                    $border->bottom,
                    $border->right,
                    $br,
                    $h,
                    $v,
                    $style,
                );
            }
        }

        if ($this->title !== null && $border->top && $area->width > 2) {
            $this->renderTitle($area, $buffer);
        }
    }

    /**
     * @param list<ControlSequenceIntroducer> $style
     */
    private static function renderCorner(
        Buffer $buffer,
        int $x,
        int $y,
        bool $sideH,
        bool $sideV,
        string $corner,
        string $h,
        string $v,
        array $style,
    ): void {
        if ($sideH && $sideV) {
            $buffer->set($x, $y, new Cell($corner, $style));
            return;
        }

        if ($sideH) {
            $buffer->set($x, $y, new Cell($h, $style));
            return;
        }

        if ($sideV) {
            $buffer->set($x, $y, new Cell($v, $style));
        }
    }

    private function renderTitle(Rect $area, Buffer $buffer): void
    {
        /** @var non-negative-int $maxTitleWidth */
        $maxTitleWidth = $area->width - 2;
        /** @var string $title */
        $title = $this->title;
        $titleText = Str\width($title) > $maxTitleWidth ? Str\width_slice($title, 0, $maxTitleWidth) : $title;
        $titleDisplayWidth = Str\width($titleText);

        $offset = match ($this->titleAlignment) {
            Alignment::Left => 0,
            Alignment::Center => (int) (($maxTitleWidth - $titleDisplayWidth) / 2),
            Alignment::Right => $maxTitleWidth - $titleDisplayWidth,
        };

        $style = $this->titleStyle;

        $chars = Str\chunk($titleText);
        $col = $offset;
        foreach ($chars as $char) {
            $charWidth = Str\width($char);
            $buffer->set($area->x + 1 + $col, $area->y, new Cell($char, $style));
            for ($w = 1; $w < $charWidth; $w++) {
                $buffer->set($area->x + 1 + $col + $w, $area->y, new Cell('', $style));
            }

            $col += $charWidth;
        }
    }
}
