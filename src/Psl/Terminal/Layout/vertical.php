<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

use Psl\Terminal\Frame;
use Psl\Terminal\Rect;

/**
 * Split a rectangular area into horizontal strips stacked vertically.
 *
 * @param Frame|Rect $area The area to split.
 * @param list<Constraint> $constraints The sizing constraints for each strip.
 *
 * @return list<Rect> The resulting rects, one per constraint.
 */
function vertical(Frame|Rect $area, array $constraints): array
{
    $rect = $area instanceof Frame ? $area->rect() : $area;

    return Internal\solve($rect, $constraints, vertical: true);
}
