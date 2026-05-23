<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Internal;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;

final class ResolveProtocolVersionsTest extends TestCase
{
    public function testDefaultVersionsWhenRequestHasDefault(): void
    {
        $request = new Request(method: 'GET', url: null, protocolVersion: ProtocolVersion::V11);
        $config = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20, ProtocolVersion::V11]);

        $result = Internal\resolve_protocol_versions($request, $config);

        static::assertSame([ProtocolVersion::V20, ProtocolVersion::V11], $result);
    }

    public function testExplicitVersionConstrainsToSingle(): void
    {
        $request = new Request(method: 'GET', url: null, protocolVersion: ProtocolVersion::V20);
        $config = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20, ProtocolVersion::V11]);

        $result = Internal\resolve_protocol_versions($request, $config);

        static::assertSame([ProtocolVersion::V20], $result);
    }

    public function testExplicitVersionNotInConfigThrows(): void
    {
        $request = new Request(method: 'GET', url: null, protocolVersion: ProtocolVersion::V20);
        $config = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Unsupported protocol version');

        Internal\resolve_protocol_versions($request, $config);
    }

    public function testExplicitH3Throws(): void
    {
        $request = new Request(method: 'GET', url: null, protocolVersion: ProtocolVersion::V30);
        $config = new ClientConfiguration(protocolVersions: [ProtocolVersion::V30, ProtocolVersion::V11]);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Unsupported protocol version');

        Internal\resolve_protocol_versions($request, $config);
    }

    public function testEmptyConfigVersionsThrows(): void
    {
        $request = new Request(method: 'GET', url: null, protocolVersion: ProtocolVersion::V11);
        $config = new ClientConfiguration(protocolVersions: []);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('No protocol versions configured');

        Internal\resolve_protocol_versions($request, $config);
    }

    public function testV10Explicit(): void
    {
        $request = new Request(method: 'GET', url: null, protocolVersion: ProtocolVersion::V10);
        $config = new ClientConfiguration(protocolVersions: [ProtocolVersion::V10]);

        $result = Internal\resolve_protocol_versions($request, $config);

        static::assertSame([ProtocolVersion::V10], $result);
    }
}
