<?php

declare(strict_types=1);

namespace Psl\DateTime;

use Psl\Default\DefaultInterface;

/**
 * Enum representing date format styles.
 *
 * This enum is used to specify the style of date formatting operations, allowing for varying levels of detail.
 * The styles range from no date (None) to a fully detailed date representation (Full).
 *
 * - None: No date formatting.
 * - Short: Short format that typically includes numeric values (e.g., 12/31/2020).
 * - Medium: Medium format that provides a balance between brevity and detail (e.g., Jan 31, 2020).
 * - Long: Long format that includes full month names and often includes the day of the week (e.g., Friday, January 31, 2020).
 * - Full: Full format that provides the most detail, often including the full day and month names, and the year in full (e.g., Friday, January 31, 2020).
 *
 * The default format style is Medium.
 */
enum DateStyle implements DefaultInterface
{
    case None;
    case Short;
    case Medium;
    case Long;
    case Full;

    /**
     * Returns the default date format style.
     *
     * This method implements the DefaultInterface, providing a standard way to access the default enum case.
     * The Medium style is returned as the default, representing a balance between detail and brevity.
     *
     * @return static The default date format style.
     *
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return self::Medium;
    }
}
