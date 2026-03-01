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
 * A horizontal progress bar widget.
 *
 * Renders a single-row gauge: `label [████░░░░] XX%`
 */
final class Gauge implements WidgetInterface
{
    private float $ratio = 0.0;
    private string $label = '';
    private Style $filledStyle;
    private Style $emptyStyle;
    private Style $labelStyle;

    private function __construct()
    {
        $this->filledStyle = new Style();
        $this->emptyStyle = new Style();
        $this->labelStyle = new Style();
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the fill ratio (clamped to 0.0–1.0).
     */
    public function ratio(float $ratio): self
    {
        $this->ratio = Math\clamp($ratio, 0.0, 1.0);
        return $this;
    }

    /**
     * Set the label displayed to the left of the bar.
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set the style for the filled portion of the bar.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function filledStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->filledStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    /**
     * Set the style for the empty portion of the bar.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function emptyStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->emptyStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    /**
     * Set the style for the label text.
     */
    /**
     * @param list<ControlSequenceIntroducer> $modifiers
     */
    public function labelStyle(
        null|Color $foreground = null,
        null|Color $background = null,
        null|ControlSequenceIntroducer $style = null,
        array $modifiers = [],
    ): self {
        if ($style !== null) {
            $modifiers[] = $style;
        }

        $this->labelStyle = new Style($foreground, $background, $modifiers);

        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $y = $area->y;

        $labelText = $this->label !== '' ? $this->label . ' ' : '';
        $labelLen = Str\width($labelText);

        $pct = (int) Math\round($this->ratio * 100.0);
        $pctText = ' ' . $pct . '%';
        $pctLen = Str\width($pctText);

        $barWidth = Math\maxva(0, $area->width - $labelLen - $pctLen);

        $x = $area->x;
        if ($labelText !== '') {
            $buffer->setString(
                $x,
                $y,
                $labelText,
                $this->labelStyle->foreground,
                $this->labelStyle->background,
                $this->labelStyle->modifiers,
            );
            $x += $labelLen;
        }

        $filledWidth = (int) Math\round($this->ratio * (float) $barWidth);
        $emptyWidth = $barWidth - $filledWidth;

        for ($i = 0; $i < $filledWidth; $i++) {
            if ($x >= $area->right()) {
                break;
            }

            $buffer->set(
                $x,
                $y,
                new Cell(
                    "\u{2588}",
                    $this->filledStyle->foreground,
                    $this->filledStyle->background,
                    $this->filledStyle->modifiers,
                ),
            );
            $x++;
        }

        for ($i = 0; $i < $emptyWidth; $i++) {
            if ($x >= $area->right()) {
                break;
            }

            $buffer->set(
                $x,
                $y,
                new Cell(
                    "\u{2591}",
                    $this->emptyStyle->foreground,
                    $this->emptyStyle->background,
                    $this->emptyStyle->modifiers,
                ),
            );
            $x++;
        }

        if ($x < $area->right()) {
            $buffer->setString(
                $x,
                $y,
                $pctText,
                $this->labelStyle->foreground,
                $this->labelStyle->background,
                $this->labelStyle->modifiers,
            );
        }
    }
}
