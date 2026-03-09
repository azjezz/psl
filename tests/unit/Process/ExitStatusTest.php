<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Process;

use PHPUnit\Framework\TestCase;
use Psl\Process\ExitStatus;
use Psl\Process\Signal;

final class ExitStatusTest extends TestCase
{
    public function testSuccessfulExit(): void
    {
        $status = new ExitStatus(0);

        static::assertTrue($status->isSuccessful());
        static::assertSame(0, $status->getCode());
        static::assertFalse($status->hasBeenSignaled());
        static::assertNull($status->getTerminationSignal());
    }

    public function testFailedExit(): void
    {
        $status = new ExitStatus(1);

        static::assertFalse($status->isSuccessful());
        static::assertSame(1, $status->getCode());
        static::assertFalse($status->hasBeenSignaled());
        static::assertNull($status->getTerminationSignal());
    }

    public function testSignaledExit(): void
    {
        $status = new ExitStatus(137, true, 9);

        static::assertFalse($status->isSuccessful());
        static::assertSame(137, $status->getCode());
        static::assertTrue($status->hasBeenSignaled());
        static::assertSame(Signal::Kill, $status->getTerminationSignal());
    }

    public function testSignaledWithUnknownSignal(): void
    {
        $status = new ExitStatus(200, true, 999);

        static::assertTrue($status->hasBeenSignaled());
        static::assertNull($status->getTerminationSignal());
    }
}
