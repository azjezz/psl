<?php

declare(strict_types=1);

namespace Psl\Process\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\Process\Stdio;

final class StdioTest extends TestCase
{
    public function testPiped(): void
    {
        $stdio = Stdio::piped();

        static::assertTrue($stdio->isPiped());
        static::assertFalse($stdio->isInherit());
        static::assertFalse($stdio->isNull());
        static::assertFalse($stdio->isHandle());
        static::assertNull($stdio->getHandle());
    }

    public function testInherit(): void
    {
        $stdio = Stdio::inherit();

        static::assertFalse($stdio->isPiped());
        static::assertTrue($stdio->isInherit());
        static::assertFalse($stdio->isNull());
        static::assertFalse($stdio->isHandle());
        static::assertNull($stdio->getHandle());
    }

    public function testNull(): void
    {
        $stdio = Stdio::null();

        static::assertFalse($stdio->isPiped());
        static::assertFalse($stdio->isInherit());
        static::assertTrue($stdio->isNull());
        static::assertFalse($stdio->isHandle());
        static::assertNull($stdio->getHandle());
    }

    public function testTty(): void
    {
        $stdio = Stdio::tty();

        static::assertFalse($stdio->isPiped());
        static::assertFalse($stdio->isInherit());
        static::assertFalse($stdio->isNull());
        static::assertFalse($stdio->isHandle());
        static::assertTrue($stdio->isTty());
        static::assertNull($stdio->getHandle());
    }

    public function testPipedIsNotTty(): void
    {
        static::assertFalse(Stdio::piped()->isTty());
    }

    public function testInheritIsNotTty(): void
    {
        static::assertFalse(Stdio::inherit()->isTty());
    }

    public function testNullIsNotTty(): void
    {
        static::assertFalse(Stdio::null()->isTty());
    }

    public function testFromStreamHandle(): void
    {
        [$read, $write] = IO\pipe();
        $stdio = Stdio::fromStreamHandle($read);

        static::assertFalse($stdio->isPiped());
        static::assertFalse($stdio->isInherit());
        static::assertFalse($stdio->isNull());
        static::assertFalse($stdio->isTty());
        static::assertTrue($stdio->isHandle());
        static::assertSame($read, $stdio->getHandle());

        $read->close();
        $write->close();
    }

    public function testFromStreamHandleIsNotTty(): void
    {
        [$read, $write] = IO\pipe();
        $stdio = Stdio::fromStreamHandle($read);

        static::assertFalse($stdio->isTty());

        $read->close();
        $write->close();
    }
}
