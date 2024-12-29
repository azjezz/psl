<?php

declare(strict_types=1);

namespace Psl\DateTime\Exception;

use Psl\DateTime\Month;
use Psl\Exception;
use Psl\Str;

final class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    /**
     * An unexpected year value.
     *
     * @param int $value The year value that was provided.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forYear(int $value): self
    {
        return new self(Str\format(
            'The year \'%d\' diverges from expectation; a positive integer is required.',
            $value,
        ));
    }

    /**
     * An unexpected month value.
     *
     * @param int $value The month value that was provided.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forMonth(int $value): self
    {
        return new self(Str\format('The month \'%d\' falls outside the acceptable range of \'1\' to \'12\'.', $value));
    }

    /**
     * An unexpected day value, considering the month and year.
     *
     * @param int $value The day value that was provided.
     * @param int $month The month context for the day value.
     * @param int $year The year context for the day value.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forDay(int $value, int $month, int $year): self
    {
        return new self(Str\format(
            'The day \'%d\', for month \'%d\' and year \'%d\', does not align with the expected range of \'1\' to \'%d\'.',
            $value,
            $month,
            $year,
            Month::from($month)->getDaysForYear($year),
        ));
    }

    /**
     * An unexpected hours value.
     *
     * @param int $value The hours value that was provided.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forHours(int $value): self
    {
        return new self(Str\format('The hour \'%d\' exceeds the expected range of \'0\' to \'23\'.', $value));
    }

    /**
     * An unexpected minutes value.
     *
     * @param int $value The minutes value that was provided.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forMinutes(int $value): self
    {
        return new self(Str\format('The minute \'%d\' steps beyond the bounds of \'0\' to \'59\'.', $value));
    }

    /**
     * An unexpected seconds value.
     *
     * @param int $value The seconds value that was provided.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forSeconds(int $value): self
    {
        return new self(Str\format(
            'The seconds \'%d\' stretch outside the acceptable range of \'0\' to \'59\'.',
            $value,
        ));
    }

    /**
     * An unexpected nanoseconds value.
     *
     * @param int $value The nanoseconds value that was provided.
     *
     * @return self Instance encapsulating the exception context.
     *
     * @psalm-mutation-free
     *
     * @internal
     */
    public static function forNanoseconds(int $value): self
    {
        return new self(Str\format(
            'The nanoseconds \'%d\' exceed the foreseen limit of \'0\' to \'999999999\'.',
            $value,
        ));
    }
}
