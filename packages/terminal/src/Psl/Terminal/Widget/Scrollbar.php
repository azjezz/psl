<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Math;
use Psl\Terminal\Buffer;
use Psl\Terminal\Cell;
use Psl\Terminal\Rect;

use function array_values;

/**
 * A vertical scrollbar widget.
 *
 * Renders a 1-character-wide vertical scrollbar with a track (`░`) and thumb (`█`).
 * The thumb size and position are calculated from content length, viewport length, and scroll position.
 */
final class Scrollbar implements WidgetInterface
{
    private int $contentLength = 0;
    private int $viewportLength = 0;
    private int $position = 0;

    /** @var list<ControlSequenceIntroducer> */
    private array $thumbStyle = [];

    /** @var list<ControlSequenceIntroducer> */
    private array $trackStyle = [];

    private function __construct() {}

    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the total content length (total number of items/rows).
     */
    public function contentLength(int $length): self
    {
        $this->contentLength = Math\maxva(0, $length);
        return $this;
    }

    /**
     * Set the viewport length (number of visible items/rows).
     */
    public function viewportLength(int $length): self
    {
        $this->viewportLength = Math\maxva(0, $length);
        return $this;
    }

    /**
     * Set the current scroll position (index of the first visible item).
     */
    public function position(int $position): self
    {
        $this->position = Math\maxva(0, $position);
        return $this;
    }

    /**
     * Set the style for the thumb (position indicator).
     */
    public function thumbStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->thumbStyle = array_values($style);

        return $this;
    }

    /**
     * Set the style for the track background.
     */
    public function trackStyle(ControlSequenceIntroducer ...$style): self
    {
        $this->trackStyle = array_values($style);

        return $this;
    }

    public function render(Rect $area, Buffer $buffer): void
    {
        if ($area->isEmpty()) {
            return;
        }

        $trackHeight = $area->height;
        $x = $area->x;

        if ($this->contentLength <= $this->viewportLength || $this->contentLength <= 0) {
            for ($i = 0; $i < $trackHeight; $i++) {
                $buffer->set($x, $area->y + $i, new Cell("\u{2503}", $this->thumbStyle));
            }

            return;
        }

        $thumbSize = Math\maxva(
            1,
            (int) Math\round(((float) $this->viewportLength / (float) $this->contentLength) * (float) $trackHeight),
        );
        $maxPosition = $this->contentLength - $this->viewportLength;
        $clampedPosition = Math\clamp($this->position, 0, $maxPosition);
        $thumbOffset = $maxPosition > 0
            ? (int) Math\round(((float) $clampedPosition / (float) $maxPosition) * (float) ($trackHeight - $thumbSize))
            : 0;
        $thumbOffset = Math\clamp($thumbOffset, 0, $trackHeight - $thumbSize);

        for ($i = 0; $i < $trackHeight; $i++) {
            $isThumb = $i >= $thumbOffset && $i < ($thumbOffset + $thumbSize);
            $style = $isThumb ? $this->thumbStyle : $this->trackStyle;
            $char = $isThumb ? "\u{2503}" : "\u{2502}";

            $buffer->set($x, $area->y + $i, new Cell($char, $style));
        }
    }
}
