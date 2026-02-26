<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Process;

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

    public function testFromStreamHandle(): void
    {
        [$read, $write] = IO\pipe();
        $stdio = Stdio::fromStreamHandle($read);

        static::assertFalse($stdio->isPiped());
        static::assertFalse($stdio->isInherit());
        static::assertFalse($stdio->isNull());
        static::assertTrue($stdio->isHandle());
        static::assertSame($read, $stdio->getHandle());

        $read->close();
        $write->close();
    }
}
