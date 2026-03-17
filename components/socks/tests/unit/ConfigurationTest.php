<?php

declare(strict_types=1);

namespace Psl\Socks\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Socks;

final class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new Socks\Configuration('proxy.example.com', 1080);

        static::assertSame('proxy.example.com', $config->proxyHost);
        static::assertSame(1080, $config->proxyPort);
        static::assertNull($config->username);
        static::assertNull($config->password);
    }

    public function testWithCredentials(): void
    {
        $config = new Socks\Configuration('proxy.example.com', 1080);
        $new = $config->withCredentials('user', 'pass');

        static::assertNull($config->username);
        static::assertNull($config->password);
        static::assertSame('user', $new->username);
        static::assertSame('pass', $new->password);
    }

    public function testWithProxyHost(): void
    {
        $config = new Socks\Configuration('old.example.com', 1080);
        $new = $config->withProxyHost('new.example.com');

        static::assertSame('old.example.com', $config->proxyHost);
        static::assertSame('new.example.com', $new->proxyHost);
        static::assertSame(1080, $new->proxyPort);
    }

    public function testWithProxyPort(): void
    {
        $config = new Socks\Configuration('proxy.example.com', 1080);
        $new = $config->withProxyPort(9050);

        static::assertSame(1080, $config->proxyPort);
        static::assertSame(9050, $new->proxyPort);
        static::assertSame('proxy.example.com', $new->proxyHost);
    }

    public function testChaining(): void
    {
        $config = new Socks\Configuration('proxy.example.com', 1080)
            ->withProxyHost('tor.local')
            ->withProxyPort(9050)
            ->withCredentials('admin', 'secret');

        static::assertSame('tor.local', $config->proxyHost);
        static::assertSame(9050, $config->proxyPort);
        static::assertSame('admin', $config->username);
        static::assertSame('secret', $config->password);
    }

    public function testWithCredentialsNull(): void
    {
        $config = new Socks\Configuration('proxy.example.com', 1080, 'user', 'pass');
        $new = $config->withCredentials(null, null);

        static::assertSame('user', $config->username);
        static::assertNull($new->username);
        static::assertNull($new->password);
    }

    public function testImmutability(): void
    {
        $original = new Socks\Configuration('proxy.example.com', 1080);
        $modified = $original->withProxyPort(9050);

        static::assertSame(1080, $original->proxyPort);
        static::assertSame(9050, $modified->proxyPort);
        static::assertNotSame($original, $modified);
    }
}
