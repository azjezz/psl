<?php

declare(strict_types=1);

namespace Psl\HTTP\Message\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Message\ProtocolVersion;

final class ProtocolVersionTest extends TestCase
{
    public function testV10(): void
    {
        static::assertSame('HTTP/1.0', ProtocolVersion::V10->value);
    }

    public function testV11(): void
    {
        static::assertSame('HTTP/1.1', ProtocolVersion::V11->value);
    }

    public function testV20(): void
    {
        static::assertSame('HTTP/2', ProtocolVersion::V20->value);
    }

    public function testV30(): void
    {
        static::assertSame('HTTP/3', ProtocolVersion::V30->value);
    }

    public function testCaseCount(): void
    {
        static::assertCount(4, ProtocolVersion::cases());
    }
}
