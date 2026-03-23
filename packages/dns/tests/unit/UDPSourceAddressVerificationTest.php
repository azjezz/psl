<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Binary\Reader;
use Psl\Binary\Writer;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Record\RecordType;
use Psl\DNS\UDPResolver;
use Psl\UDP;

final class UDPSourceAddressVerificationTest extends TestCase
{
    public function testRejectsResponseFromDifferentSourceAddress(): void
    {
        $targetServer = UDP\Socket::bind('127.0.0.1', 0);
        $targetAddress = $targetServer->getLocalAddress();

        $spoofServer = UDP\Socket::bind('127.0.0.1', 0);

        Async\run(static function () use ($targetServer, $spoofServer): void {
            [$query, $clientPeer] = $targetServer->receiveFrom(512);
            $reader = new Reader($query);
            $id = $reader->u16();

            $responsePacket = new Writer()
                ->u16($id)
                ->u16(0x8180)
                ->u16(0)
                ->u16(0)
                ->u16(0)
                ->u16(0)
                ->toString();

            $spoofServer->sendTo($responsePacket, $clientPeer);
            $targetServer->close();
            $spoofServer->close();
        });

        $spoofAddress = $spoofServer->getLocalAddress();

        try {
            $resolver = new UDPResolver(host: $targetAddress->host, port: $targetAddress->port ?? 0);
            $resolver->query('test.example', RecordType::A);
            static::fail('Expected ResponseMismatchException');
        } catch (ProtocolException $e) {
            $expectedPort = (string) ($spoofAddress->port ?? 0);
            $expectedSuffix = $spoofAddress->host . ':' . $expectedPort;
            static::assertStringContainsString('DNS response received from', $e->getMessage());
            static::assertStringContainsString($expectedSuffix, $e->getMessage());
        }
    }
}
