<?php

declare(strict_types=1);

namespace Psl\TCP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\TCP;

final class ListenConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new TCP\ListenConfiguration();

        static::assertFalse($config->noDelay);
        static::assertFalse($config->reuseAddress);
        static::assertFalse($config->reusePort);
        static::assertSame(512, $config->backlog);
        static::assertSame(256, $config->idleConnections);
        static::assertNull($config->bindTo);
    }

    public function testDefaultMethod(): void
    {
        $config = TCP\ListenConfiguration::default();

        static::assertFalse($config->noDelay);
        static::assertSame(512, $config->backlog);
    }

    public function testWithNoDelay(): void
    {
        $config = new TCP\ListenConfiguration();
        $new = $config->withNoDelay(true);

        static::assertFalse($config->noDelay);
        static::assertTrue($new->noDelay);
        static::assertSame($config->backlog, $new->backlog);
    }

    public function testWithReuseAddress(): void
    {
        $config = new TCP\ListenConfiguration();
        $new = $config->withReuseAddress(true);

        static::assertFalse($config->reuseAddress);
        static::assertTrue($new->reuseAddress);
    }

    public function testWithReusePort(): void
    {
        $config = new TCP\ListenConfiguration();
        $new = $config->withReusePort(true);

        static::assertFalse($config->reusePort);
        static::assertTrue($new->reusePort);
    }

    public function testWithBacklog(): void
    {
        $config = new TCP\ListenConfiguration();
        $new = $config->withBacklog(1024);

        static::assertSame(512, $config->backlog);
        static::assertSame(1024, $new->backlog);
    }

    public function testWithIdleConnections(): void
    {
        $config = new TCP\ListenConfiguration();
        $new = $config->withIdleConnections(128);

        static::assertSame(256, $config->idleConnections);
        static::assertSame(128, $new->idleConnections);
    }

    public function testWithBindTo(): void
    {
        $config = new TCP\ListenConfiguration();
        $new = $config->withBindTo('0.0.0.0:8080');

        static::assertNull($config->bindTo);
        static::assertSame('0.0.0.0:8080', $new->bindTo);
    }

    public function testWithBindToNull(): void
    {
        $config = new TCP\ListenConfiguration(bindTo: '0.0.0.0:8080');
        $new = $config->withBindTo(null);

        static::assertSame('0.0.0.0:8080', $config->bindTo);
        static::assertNull($new->bindTo);
    }

    public function testWithMethodsPreserveBindTo(): void
    {
        $config = new TCP\ListenConfiguration(bindTo: '10.0.0.1:0');

        static::assertSame('10.0.0.1:0', $config->withNoDelay(true)->bindTo);
        static::assertSame('10.0.0.1:0', $config->withReuseAddress(true)->bindTo);
        static::assertSame('10.0.0.1:0', $config->withReusePort(true)->bindTo);
        static::assertSame('10.0.0.1:0', $config->withBacklog(1024)->bindTo);
        static::assertSame('10.0.0.1:0', $config->withIdleConnections(64)->bindTo);
    }

    public function testBindToIsUsedByListen(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(bindTo: '127.0.0.1:0'));
        $local = $listener->getLocalAddress();

        static::assertSame('127.0.0.1', $local->host);
        static::assertGreaterThan(0, $local->port);

        $listener->close();
    }

    public function testChaining(): void
    {
        $config = TCP\ListenConfiguration::default()
            ->withNoDelay(true)
            ->withReuseAddress(true)
            ->withBacklog(2048)
            ->withIdleConnections(64)
            ->withBindTo('0.0.0.0:9090');

        static::assertTrue($config->noDelay);
        static::assertTrue($config->reuseAddress);
        static::assertFalse($config->reusePort);
        static::assertSame(2048, $config->backlog);
        static::assertSame(64, $config->idleConnections);
        static::assertSame('0.0.0.0:9090', $config->bindTo);
    }
}
