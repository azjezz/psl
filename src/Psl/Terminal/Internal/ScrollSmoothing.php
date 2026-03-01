<?php

declare(strict_types=1);

namespace Psl\Terminal\Internal;

use Psl\Terminal\Event;

/**
 * Filters out trackpad scroll micro-reversals.
 *
 * Trackpad smooth scrolling generates brief opposite-direction events when
 * finger speed changes, causing visible flicker. This filter requires 2
 * consecutive events in the new direction before accepting a direction change.
 *
 * @internal
 */
final class ScrollSmoothing
{
    private null|Event\MouseKind $direction = null;
    private int $reverseCount = 0;

    /**
     * Returns true if the event should be dispatched, false to suppress.
     *
     * Non-scroll mouse events always pass through.
     */
    public function filter(Event\Mouse $event): bool
    {
        if ($event->kind !== Event\MouseKind::ScrollUp && $event->kind !== Event\MouseKind::ScrollDown) {
            return true;
        }

        if ($this->direction === null || $this->direction === $event->kind) {
            $this->direction = $event->kind;
            $this->reverseCount = 0;

            return true;
        }

        $this->reverseCount++;
        if ($this->reverseCount >= 2) {
            $this->direction = $event->kind;
            $this->reverseCount = 0;

            return true;
        }

        return false;
    }
}
