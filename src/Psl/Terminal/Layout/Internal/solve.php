<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout\Internal;

use Psl\Iter;
use Psl\Math;
use Psl\Terminal\Layout\Constraint;
use Psl\Terminal\Rect;
use Psl\Vec;

/**
 * Solve layout constraints along a single axis.
 *
 * @param list<Constraint> $constraints
 *
 * @return list<Rect>
 *
 * @internal
 */
function solve(Rect $rect, array $constraints, bool $vertical): array
{
    $totalSpace = $vertical ? $rect->height : $rect->width;

    if ($constraints === []) {
        return [];
    }

    $count = Iter\count($constraints);
    /** @var list<int> $sizes */
    $sizes = Vec\fill($count, 0);
    $rawSizes = Vec\map($constraints, static fn(Constraint $constraint): int => resolve_constraint(
        $constraint,
        $totalSpace,
    ));

    $fixedTotal = 0;
    $fillCount = 0;
    foreach ($rawSizes as $size) {
        if ($size === -1) {
            $fillCount++;
            continue;
        }

        $fixedTotal += $size;
    }

    $remaining = Math\maxva(0, $totalSpace - $fixedTotal);
    $fillSize = $fillCount > 0 ? (int) ($remaining / $fillCount) : 0;
    $fillRemainder = $fillCount > 0 ? $remaining % $fillCount : 0;

    $fillIndex = 0;
    foreach ($rawSizes as $i => $size) {
        if ($size === -1) {
            $sizes[$i] = $fillSize + ($fillIndex < $fillRemainder ? 1 : 0);
            $fillIndex++;
            continue;
        }

        $sizes[$i] = $size;
    }

    $totalAllocated = Math\sum($sizes);
    if ($totalAllocated > $totalSpace) {
        $excess = $totalAllocated - $totalSpace;
        $shrinkableTotal = $totalAllocated;
        if ($shrinkableTotal > 0) {
            $remaining = $excess;
            for ($i = $count - 1; $i >= 0 && $remaining > 0; $i--) {
                $share = (int) Math\round(($sizes[$i] / $shrinkableTotal) * $excess);
                $reduction = Math\minva($sizes[$i], $share, $remaining);
                $sizes[$i] -= $reduction;
                $remaining -= $reduction;
            }
        }
    }

    $result = [];
    $offset = 0;
    foreach ($sizes as $size) {
        $result[] = $vertical
            ? new Rect($rect->x, $rect->y + $offset, $rect->width, $size)
            : new Rect($rect->x + $offset, $rect->y, $size, $rect->height);

        $offset += $size;
    }

    return $result;
}
