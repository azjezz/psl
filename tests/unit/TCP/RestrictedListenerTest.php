<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\CIDR;
use Psl\DateTime\Duration;
use Psl\IP;
use Psl\Network;
use Psl\TCP;

final class RestrictedListenerTest extends TestCase
{
    public function testAcceptsAllowedIpAddress(): void
    {
        Async\concurrently([
            'server' => static function (): void {
                $inner = TCP\listen('127.0.0.1', 18_200);
                $listener = new TCP\RestrictedListener($inner, [
                    IP\Address::parse('127.0.0.1'),
                ]);

                $stream = $listener->accept();
                $data = $stream->read();
                static::assertSame('allowed', $data);
                $stream->close();
                $listener->close();
            },
            'client' => static function (): void {
                $stream = TCP\connect('127.0.0.1', 18_200);
                $stream->writeAll('allowed');
                $stream->close();
            },
        ]);
    }

    public function testAcceptsAllowedCidrBlock(): void
    {
        Async\concurrently([
            'server' => static function (): void {
                $inner = TCP\listen('127.0.0.1', 18_201);
                $listener = new TCP\RestrictedListener($inner, [
                    new CIDR\Block('127.0.0.0/8'),
                ]);

                $stream = $listener->accept();
                $data = $stream->read();
                static::assertSame('cidr-allowed', $data);
                $stream->close();
                $listener->close();
            },
            'client' => static function (): void {
                $stream = TCP\connect('127.0.0.1', 18_201);
                $stream->writeAll('cidr-allowed');
                $stream->close();
            },
        ]);
    }

    public function testRejectsDisallowedAndTimesOut(): void
    {
        $inner = TCP\listen('127.0.0.1', 18_202);
        $listener = new TCP\RestrictedListener($inner, [
            new CIDR\Block('10.0.0.0/8'),
        ]);

        // Connect a client in the background (will be rejected)
        Async\run(static function (): void {
            try {
                $stream = TCP\connect('127.0.0.1', 18_202);
                $stream->close();
            } catch (Network\Exception\RuntimeException) {
                static::addToAssertionCount(1);
            }
        })->ignore();

        $token = new Async\TimeoutCancellationToken(Duration::milliseconds(100));

        try {
            $listener->accept($token);
            static::fail('Should have been cancelled');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        }

        $listener->close();
    }

    public function testDelegatesGetLocalAddress(): void
    {
        $inner = TCP\listen('127.0.0.1', 0);
        $listener = new TCP\RestrictedListener($inner, []);

        $address = $listener->getLocalAddress();

        static::assertSame('127.0.0.1', $address->host);

        $listener->close();
    }

    public function testDelegatesIsClosed(): void
    {
        $inner = TCP\listen('127.0.0.1', 0);
        $listener = new TCP\RestrictedListener($inner, []);

        static::assertFalse($listener->isClosed());

        $listener->close();

        static::assertTrue($listener->isClosed());
    }

    public function testDelegatesClose(): void
    {
        $inner = TCP\listen('127.0.0.1', 0);
        $listener = new TCP\RestrictedListener($inner, []);

        $listener->close();

        static::assertTrue($inner->isClosed());
    }

    public function testAcceptWithCancellation(): void
    {
        $inner = TCP\listen('127.0.0.1', 0);
        $listener = new TCP\RestrictedListener($inner, [
            IP\Address::parse('127.0.0.1'),
        ]);

        $token = new Async\TimeoutCancellationToken(Duration::milliseconds(10));

        Async\run(static function () use ($inner): void {
            Async\sleep(Duration::seconds(5));
            $inner->close();
        })->ignore();

        try {
            Async\run(static function () use ($listener, $token): void {
                $listener->accept($token);
            })->await();

            static::fail('Expected CancelledException');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        } finally {
            $listener->close();
        }
    }

    public function testEmptyAllowListRejectsAll(): void
    {
        $inner = TCP\listen('127.0.0.1', 18_203);
        $listener = new TCP\RestrictedListener($inner, []);

        Async\run(static function (): void {
            try {
                $stream = TCP\connect('127.0.0.1', 18_203);
                $stream->close();
            } catch (Network\Exception\RuntimeException) {
                static::addToAssertionCount(1);
            }
        })->ignore();

        $token = new Async\TimeoutCancellationToken(Duration::milliseconds(100));

        try {
            $listener->accept($token);
            static::fail('Should have been cancelled');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        }

        $listener->close();
    }

    public function testMixedIpAndCidrAllowList(): void
    {
        Async\concurrently([
            'server' => static function (): void {
                $inner = TCP\listen('127.0.0.1', 18_204);
                $listener = new TCP\RestrictedListener($inner, [
                    IP\Address::parse('10.0.0.1'),
                    new CIDR\Block('127.0.0.0/8'),
                ]);

                $stream = $listener->accept();
                $data = $stream->read();
                static::assertSame('mixed-allow', $data);
                $stream->close();
                $listener->close();
            },
            'client' => static function (): void {
                $stream = TCP\connect('127.0.0.1', 18_204);
                $stream->writeAll('mixed-allow');
                $stream->close();
            },
        ]);
    }
}
