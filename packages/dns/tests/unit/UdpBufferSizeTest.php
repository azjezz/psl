<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DNS\UDPResolver;
use ReflectionClass;

final class UdpBufferSizeTest extends TestCase
{
    public function testDefaultBufferSizeIs1232(): void
    {
        $resolver = new UDPResolver(host: '127.0.0.1');

        $reflection = new ReflectionClass($resolver);
        $property = $reflection->getProperty('udpPayloadSize');

        static::assertSame(1232, $property->getValue($resolver));
    }

    public function testCustomBufferSizeIsRespected(): void
    {
        $resolver = new UDPResolver(host: '127.0.0.1', udpPayloadSize: 4096);

        $reflection = new ReflectionClass($resolver);
        $property = $reflection->getProperty('udpPayloadSize');

        static::assertSame(4096, $property->getValue($resolver));
    }
}
