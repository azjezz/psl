<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\Connector;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\Socks;

use function Psl\URL\parse;

final class SocksProxyTest extends TestCase
{
    public function testPooledConnectorUsesProxyForConnection(): void
    {
        $client = new Client(
            connector: new PooledConnector(),
            configuration: new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                socksConfiguration: new Socks\Configuration('127.0.0.1', 1),
            ),
        );

        $this->expectTransportError();
        $client->send(new Request(method: 'GET', url: parse('https://example.com/')));
    }

    public function testNonPooledConnectorUsesProxyForConnection(): void
    {
        $client = new Client(
            connector: new Connector(),
            configuration: new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                socksConfiguration: new Socks\Configuration('127.0.0.1', 1),
            ),
        );

        $this->expectTransportError();
        $client->send(new Request(method: 'GET', url: parse('https://example.com/')));
    }

    public function testProxyNotUsedWhenNull(): void
    {
        $client = new Client(
            connector: new PooledConnector(),
            configuration: new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]),
        );

        $this->expectTransportError();
        $client->send(new Request(method: 'GET', url: parse('http://127.0.0.1:1/')));
    }

    private function expectTransportError(): void
    {
        $this->expectException(\Psl\Exception\RuntimeException::class);
    }
}
