<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Network;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\Network;
use Psl\TCP;

final class CompositeListenerTest extends TestCase
{
    public function testAcceptsFromMultipleListeners(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 18_300);
        $listener2 = TCP\listen('127.0.0.1', 18_301);

        $composite = new Network\CompositeListener([$listener1, $listener2]);

        Async\concurrently([
            'server' => static function () use ($composite): void {
                $stream1 = $composite->accept();
                $data1 = $stream1->read();
                $stream1->close();

                $stream2 = $composite->accept();
                $data2 = $stream2->read();
                $stream2->close();

                $values = [$data1, $data2];
                sort($values);
                static::assertSame(['from-listener-1', 'from-listener-2'], $values);

                $composite->close();
            },
            'clients' => static function (): void {
                $c1 = TCP\connect('127.0.0.1', 18_300);
                $c1->writeAll('from-listener-1');
                $c1->close();

                $c2 = TCP\connect('127.0.0.1', 18_301);
                $c2->writeAll('from-listener-2');
                $c2->close();
            },
        ]);
    }

    public function testGetLocalAddressReturnsFirst(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 18_302);
        $listener2 = TCP\listen('127.0.0.1', 18_303);

        $composite = new Network\CompositeListener([$listener1, $listener2]);

        $address = $composite->getLocalAddress();
        static::assertSame($listener1->getLocalAddress()->host, $address->host);
        static::assertSame($listener1->getLocalAddress()->port, $address->port);

        $composite->close();
    }

    public function testGetLocalAddresses(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 18_310);
        $listener2 = TCP\listen('127.0.0.1', 18_311);

        $composite = new Network\CompositeListener([$listener1, $listener2]);

        $addresses = $composite->getLocalAddresses();

        static::assertCount(2, $addresses);
        static::assertSame(18_310, $addresses[0]->port);
        static::assertSame(18_311, $addresses[1]->port);

        $composite->close();
    }

    public function testIsClosed(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 0);
        $listener2 = TCP\listen('127.0.0.1', 0);

        $composite = new Network\CompositeListener([$listener1, $listener2]);

        static::assertFalse($composite->isClosed());

        $composite->close();

        static::assertTrue($composite->isClosed());
    }

    public function testCloseClosesAllInnerListeners(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 0);
        $listener2 = TCP\listen('127.0.0.1', 0);

        $composite = new Network\CompositeListener([$listener1, $listener2]);

        $composite->close();

        static::assertTrue($listener1->isClosed());
        static::assertTrue($listener2->isClosed());
    }

    public function testAcceptWithCancellation(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 0);
        $listener2 = TCP\listen('127.0.0.1', 0);

        $composite = new Network\CompositeListener([$listener1, $listener2]);

        $token = new Async\TimeoutCancellationToken(Duration::milliseconds(50));

        try {
            Async\run(static function () use ($composite, $token): void {
                $composite->accept($token);
            })->await();

            static::fail('Expected CancelledException');
        } catch (Async\Exception\CancelledException) {
            static::addToAssertionCount(1);
        } finally {
            $composite->close();
        }
    }

    public function testAcceptOnClosedCompositeThrows(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 0);

        $composite = new Network\CompositeListener([$listener1]);
        $composite->close();

        $this->expectException(Network\Exception\AlreadyStoppedException::class);

        Async\run(static function () use ($composite): void {
            $composite->accept();
        })->await();
    }

    public function testCloseIsIdempotent(): void
    {
        $listener1 = TCP\listen('127.0.0.1', 0);

        $composite = new Network\CompositeListener([$listener1]);

        $composite->close();
        $composite->close();

        static::assertTrue($composite->isClosed());
    }

    public function testSingleListener(): void
    {
        $listener = TCP\listen('127.0.0.1', 18_304);
        $composite = new Network\CompositeListener([$listener]);

        Async\concurrently([
            'server' => static function () use ($composite): void {
                $stream = $composite->accept();
                $data = $stream->read();
                static::assertSame('single', $data);
                $stream->close();
                $composite->close();
            },
            'client' => static function (): void {
                $stream = TCP\connect('127.0.0.1', 18_304);
                $stream->writeAll('single');
                $stream->close();
            },
        ]);
    }
}
