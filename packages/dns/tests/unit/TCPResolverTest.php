<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Binary\Reader;
use Psl\Binary\Writer;
use Psl\DNS\Exception\NetworkException;
use Psl\DNS\Exception\ProtocolException;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResponseCode;
use Psl\DNS\TCPResolver;
use Psl\Str;
use Psl\TCP;
use ReflectionClass;

final class TCPResolverTest extends TestCase
{
    public function testPooledQueryReusesConnection(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $localAddress = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            for ($i = 0; $i < 2; $i++) {
                $lengthData = $client->readFixedSize(2);
                /** @var positive-int $length */
                $length = new Reader($lengthData)->u16();
                $query = $client->readFixedSize($length);
                $id = new Reader($query)->u16();

                $answerName = "\x07example\x03com\x00";
                $responsePacket = new Writer()
                    ->u16($id)
                    ->u16(0x8180)
                    ->u16(0)
                    ->u16(1)
                    ->u16(0)
                    ->u16(0)
                    ->bytes($answerName)
                    ->u16(1)
                    ->u16(1)
                    ->u32(300)
                    ->u16(4)
                    ->u8(10)
                    ->u8(0)
                    ->u8(0)
                    ->u8($i + 1)
                    ->toString();

                $tcpResponse = new Writer()
                    ->u16(Str\Byte\length($responsePacket))
                    ->bytes($responsePacket)
                    ->toString();

                $client->writeAll($tcpResponse);
            }

            $client->close();
            $server->close();
        });

        $resolver = new TCPResolver(host: $localAddress->host, port: $localAddress->port ?? 0);

        $response1 = $resolver->query('example.com', RecordType::A);
        static::assertSame(ResponseCode::NoError, $response1->code);
        static::assertCount(1, $response1->answers);
        static::assertInstanceOf(ARecord::class, $response1->answers[0]);
        static::assertSame('10.0.0.1', $response1->answers[0]->address->toString());

        $response2 = $resolver->query('example.com', RecordType::A);
        static::assertSame(ResponseCode::NoError, $response2->code);
        static::assertCount(1, $response2->answers);
        static::assertInstanceOf(ARecord::class, $response2->answers[0]);
        static::assertSame('10.0.0.2', $response2->answers[0]->address->toString());
    }

    public function testConnectionErrorDiscardedFromPool(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $localAddress = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();
            $client->close();
            $server->close();
        });

        $resolver = new TCPResolver(host: $localAddress->host, port: $localAddress->port ?? 0);

        $caughtException = false;
        try {
            $resolver->query('example.com', RecordType::A);
        } catch (NetworkException) {
            $caughtException = true;
        }

        static::assertTrue($caughtException);
    }

    public function testIdMismatchClearsConnectionFromPool(): void
    {
        $server = TCP\listen('127.0.0.1', 0);
        $localAddress = $server->getLocalAddress();

        Async\run(static function () use ($server): void {
            $client = $server->accept();

            $lengthData = $client->readFixedSize(2);
            /** @var positive-int $length */
            $length = new Reader($lengthData)->u16();
            $query = $client->readFixedSize($length);
            $id = new Reader($query)->u16();

            $wrongId = ($id + 1) & 0xFFFF;

            $answerName = "\x07example\x03com\x00";
            $responsePacket = new Writer()
                ->u16($wrongId)
                ->u16(0x8180)
                ->u16(0)
                ->u16(1)
                ->u16(0)
                ->u16(0)
                ->bytes($answerName)
                ->u16(1)
                ->u16(1)
                ->u32(300)
                ->u16(4)
                ->u8(10)
                ->u8(0)
                ->u8(0)
                ->u8(1)
                ->toString();

            $tcpResponse = new Writer()
                ->u16(Str\Byte\length($responsePacket))
                ->bytes($responsePacket)
                ->toString();

            $client->writeAll($tcpResponse);

            $client->close();
            $server->close();
        });

        $resolver = new TCPResolver(host: $localAddress->host, port: $localAddress->port ?? 0);

        try {
            $resolver->query('example.com', RecordType::A);
            static::fail('Expected ResponseMismatchException');
        } catch (ProtocolException $e) {
            static::assertStringContainsString('does not match query ID', $e->getMessage());
        }
    }

    public function testDefaultPortIs53(): void
    {
        $resolver = new TCPResolver(host: '127.0.0.1');

        $reflection = new ReflectionClass($resolver);
        $port = $reflection->getProperty('port')->getValue($resolver);

        static::assertSame(53, $port);
    }

    public function testDefaultDnssecIsFalse(): void
    {
        $resolver = new TCPResolver(host: '127.0.0.1');

        $reflection = new ReflectionClass($resolver);
        $dnssec = $reflection->getProperty('dnssec')->getValue($resolver);

        static::assertFalse($dnssec);
    }

    public function testCustomDnssecIsRespected(): void
    {
        $resolver = new TCPResolver(host: '127.0.0.1', dnssec: true);

        $reflection = new ReflectionClass($resolver);
        $dnssec = $reflection->getProperty('dnssec')->getValue($resolver);

        static::assertTrue($dnssec);
    }
}
