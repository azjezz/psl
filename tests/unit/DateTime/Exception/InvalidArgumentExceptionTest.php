<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime\Exception;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testForYear(): void
    {
        $exception = InvalidArgumentException::forYear(-1);

        static::assertSame(
            'The year \'-1\' diverges from expectation; a positive integer is required.',
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function testForMonth(): void
    {
        $exception = InvalidArgumentException::forMonth(13);

        static::assertSame(
            'The month \'13\' falls outside the acceptable range of \'1\' to \'12\'.',
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function testForDay(): void
    {
        $exception = InvalidArgumentException::forDay(32, 1, 2021);

        static::assertSame(
            'The day \'32\', for month \'1\' and year \'2021\', does not align with the expected range of \'1\' to \'31\'.',
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function testForHours(): void
    {
        $exception = InvalidArgumentException::forHours(24);

        static::assertSame('The hour \'24\' exceeds the expected range of \'0\' to \'23\'.', $exception->getMessage());

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function testForMinutes(): void
    {
        $exception = InvalidArgumentException::forMinutes(60);

        static::assertSame('The minute \'60\' steps beyond the bounds of \'0\' to \'59\'.', $exception->getMessage());

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function testForSeconds(): void
    {
        $exception = InvalidArgumentException::forSeconds(61);

        static::assertSame(
            'The seconds \'61\' stretch outside the acceptable range of \'0\' to \'59\'.',
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);
        throw $exception;
    }

    public function testForNanoseconds(): void
    {
        $exception = InvalidArgumentException::forNanoseconds(1_000_000_000);

        static::assertSame(
            'The nanoseconds \'1000000000\' exceed the foreseen limit of \'0\' to \'999999999\'.',
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);
        throw $exception;
    }
}
