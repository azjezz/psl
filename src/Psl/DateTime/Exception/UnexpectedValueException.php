<?php

declare(strict_types=1);

namespace Psl\DateTime\Exception;

use Psl\Exception;

use function sprintf;

final class UnexpectedValueException extends Exception\UnexpectedValueException implements ExceptionInterface
{
    /**
     * Exception for a mismatching year value, indicating an unexpected discrepancy between provided and expected values.
     *
     * @param int $providedYear The year value provided by the user.
     * @param int $calendarYear The year value as determined by calendar calculations.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forYear(int $providedYear, int $calendarYear): self
    {
        return new self(sprintf(
            'Unexpected year value encountered. Provided "%d", but the calendar expects "%d". Check the year for accuracy and ensure it\'s within the supported range.',
            $providedYear,
            $calendarYear,
        ));
    }

    /**
     * Exception for a mismatching month value, suggesting the provided month does not match calendar expectations.
     *
     * @param int $providedMonth The month value provided by the user.
     * @param int $calendarMonth The month value as determined by calendar calculations.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forMonth(int $providedMonth, int $calendarMonth): self
    {
        return new self(sprintf(
            'Unexpected month value encountered. Provided "%d", but the calendar expects "%d". Ensure the month is within the 1-12 range and matches the specific year context.',
            $providedMonth,
            $calendarMonth,
        ));
    }

    /**
     * Exception for a mismatching day value, highlighting a conflict between the provided and calendar-validated day.
     *
     * @param int $providedDay The day value provided by the user.
     * @param int $calendarDay The day value as confirmed by calendar validation.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forDay(int $providedDay, int $calendarDay): self
    {
        return new self(sprintf(
            'Unexpected day value encountered. Provided "%d", but the calendar expects "%d". Ensure the day is valid for the given month and year, considering variations like leap years.',
            $providedDay,
            $calendarDay,
        ));
    }

    /**
     * Exception for a mismatching hours value, indicating the provided hours do not match the expected calendar value.
     *
     * @param int $providedHours The hours value provided by the user.
     * @param int $calendarHours The hours value as determined by calendar calculations.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forHours(int $providedHours, int $calendarHours): self
    {
        return new self(sprintf(
            'Unexpected hours value encountered. Provided "%d", but the calendar expects "%d". Ensure the hour falls within a 24-hour day.',
            $providedHours,
            $calendarHours,
        ));
    }

    /**
     * Exception for a mismatching minutes value, noting a divergence between provided and expected minute values.
     *
     * @param int $providedMinutes The minutes value provided by the user.
     * @param int $calendarMinutes The minutes value as per calendar validation.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forMinutes(int $providedMinutes, int $calendarMinutes): self
    {
        return new self(sprintf(
            'Unexpected minutes value encountered. Provided "%d", but the calendar expects "%d". Check the minutes value for errors and ensure it\'s within the 0-59 range.',
            $providedMinutes,
            $calendarMinutes,
        ));
    }

    /**
     * Exception for a mismatching seconds value, indicating a difference between the provided and the calendar-validated second.
     *
     * @param int $providedSeconds The seconds value provided by the user.
     * @param int $calendarSeconds The seconds value as validated by the calendar.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forSeconds(int $providedSeconds, int $calendarSeconds): self
    {
        return new self(sprintf(
            'Unexpected seconds value encountered. Provided "%d", but the calendar expects "%d". Ensure the seconds are correct and within the 0-59 range.',
            $providedSeconds,
            $calendarSeconds,
        ));
    }
}
