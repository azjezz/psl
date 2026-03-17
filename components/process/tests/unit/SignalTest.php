<?php

declare(strict_types=1);

namespace Psl\Process\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Process\Signal;

final class SignalTest extends TestCase
{
    public function testValues(): void
    {
        static::assertSame(1, Signal::Hangup->value);
        static::assertSame(2, Signal::Interrupt->value);
        static::assertSame(3, Signal::Quit->value);
        static::assertSame(9, Signal::Kill->value);
        static::assertSame(10, Signal::User1->value);
        static::assertSame(12, Signal::User2->value);
        static::assertSame(14, Signal::Alarm->value);
        static::assertSame(15, Signal::Terminate->value);
    }

    public function testTryFrom(): void
    {
        static::assertSame(Signal::Kill, Signal::tryFrom(9));
        static::assertSame(Signal::Terminate, Signal::tryFrom(15));
        static::assertNull(Signal::tryFrom(999));
    }
}
