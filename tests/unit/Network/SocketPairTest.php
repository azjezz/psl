<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Network;

use PHPUnit\Framework\TestCase;
use Psl\Network;

use function Psl\Network\socket_pair;

final class SocketPairTest extends TestCase
{
    public function testBidirectionalCommunication(): void
    {
        [$a, $b] = socket_pair();

        $a->writeAll('hello from A');
        $data = $b->read();
        static::assertSame('hello from A', $data);

        $b->writeAll('hello from B');
        $data = $a->read();
        static::assertSame('hello from B', $data);

        $a->close();
        $b->close();
    }

    public function testCloseOneEndDetectsEof(): void
    {
        [$a, $b] = socket_pair();

        $a->writeAll('message');
        $a->close();

        $data = $b->readAll();
        static::assertSame('message', $data);

        $b->close();
    }

    public function testReturnsStreamInterfaces(): void
    {
        [$a, $b] = socket_pair();

        static::assertInstanceOf(Network\StreamInterface::class, $a);
        static::assertInstanceOf(Network\StreamInterface::class, $b);

        $a->close();
        $b->close();
    }
}
