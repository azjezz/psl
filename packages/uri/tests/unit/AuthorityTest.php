<?php

declare(strict_types=1);

namespace Psl\URI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IP\Address;
use Psl\URI\Authority\Authority;
use Psl\URI\Authority\IPHost;
use Psl\URI\Authority\RegisteredNameHost;

final class AuthorityTest extends TestCase
{
    public function testFull(): void
    {
        $authority = new Authority(
            userInfo: 'user:pass',
            host: new RegisteredNameHost(name: 'example.com'),
            port: 8080,
        );

        static::assertSame('user:pass@example.com:8080', $authority->toString());
    }

    public function testNoUserInfo(): void
    {
        $authority = new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: 8080);

        static::assertSame('example.com:8080', $authority->toString());
    }

    public function testNoPort(): void
    {
        $authority = new Authority(
            userInfo: 'user:pass',
            host: new RegisteredNameHost(name: 'example.com'),
            port: null,
        );

        static::assertSame('user:pass@example.com', $authority->toString());
    }

    public function testEmptyUserInfo(): void
    {
        $authority = new Authority(userInfo: '', host: new RegisteredNameHost(name: 'example.com'), port: null);

        static::assertSame('@example.com', $authority->toString());
    }

    public function testIPv6Host(): void
    {
        $authority = new Authority(userInfo: null, host: new IPHost(address: Address::v6('::1')), port: 443);

        static::assertSame('[::1]:443', $authority->toString());
    }

    public function testBareHost(): void
    {
        $authority = new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: null);

        static::assertSame('example.com', $authority->toString());
    }

    public function testStringable(): void
    {
        $authority = new Authority(
            userInfo: 'user:pass',
            host: new RegisteredNameHost(name: 'example.com'),
            port: 8080,
        );

        static::assertSame('user:pass@example.com:8080', (string) $authority);
    }

    public function testPortZero(): void
    {
        $authority = new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: 0);

        static::assertSame('example.com:0', $authority->toString());
        static::assertSame(0, $authority->port);
    }

    public function testPortMax(): void
    {
        $authority = new Authority(userInfo: null, host: new RegisteredNameHost(name: 'example.com'), port: 65_535);

        static::assertSame('example.com:65535', $authority->toString());
        static::assertSame(65_535, $authority->port);
    }

    public function testAllComponents(): void
    {
        $authority = new Authority(userInfo: 'u:p', host: new RegisteredNameHost(name: 'h'), port: 1);

        static::assertSame('u:p@h:1', $authority->toString());
    }

    public function testIPv4Host(): void
    {
        $authority = new Authority(userInfo: null, host: new IPHost(address: Address::v4('192.168.1.1')), port: null);

        static::assertSame('192.168.1.1', $authority->toString());
    }
}
