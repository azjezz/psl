<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

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

    public function testChaining(): void
    {
        $config = TCP\ListenConfiguration::default()
            ->withNoDelay(true)
            ->withReuseAddress(true)
            ->withBacklog(2048)
            ->withIdleConnections(64);

        static::assertTrue($config->noDelay);
        static::assertTrue($config->reuseAddress);
        static::assertFalse($config->reusePort);
        static::assertSame(2048, $config->backlog);
        static::assertSame(64, $config->idleConnections);
    }
}
