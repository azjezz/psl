<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Network;

use PHPUnit\Framework\TestCase;
use Psl\Network\Address;
use Psl\Network\SocketScheme;

final class AddressTest extends TestCase
{
    public function testAddress(): void
    {
        $address = Address::create(SocketScheme::Tcp, '127.0.0.2', 8080);

        static::assertSame(SocketScheme::Tcp, $address->scheme);
        static::assertSame('127.0.0.2', $address->host);
        static::assertSame(8080, $address->port);

        $address = Address::tcp('127.0.0.2', 8080);

        static::assertSame(SocketScheme::Tcp, $address->scheme);
        static::assertSame('127.0.0.2', $address->host);
        static::assertSame(8080, $address->port);

        $address = Address::tcp('127.0.0.2');

        static::assertSame(SocketScheme::Tcp, $address->scheme);
        static::assertSame('127.0.0.2', $address->host);
        static::assertSame(Address::DEFAULT_PORT, $address->port);

        $address = Address::tcp();

        static::assertSame(SocketScheme::Tcp, $address->scheme);
        static::assertSame(Address::DEFAULT_HOST, $address->host);
        static::assertSame(Address::DEFAULT_PORT, $address->port);

        $address = Address::create(SocketScheme::Unix, '/etc/foo');

        static::assertSame(SocketScheme::Unix, $address->scheme);
        static::assertSame('/etc/foo', $address->host);
        static::assertNull($address->port);

        $address = Address::unix('/etc/foo');

        static::assertSame(SocketScheme::Unix, $address->scheme);
        static::assertSame('/etc/foo', $address->host);
        static::assertNull($address->port);
    }
}
