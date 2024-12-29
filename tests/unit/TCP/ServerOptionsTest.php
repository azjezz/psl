<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\Network\SocketOptions;
use Psl\TCP\ServerOptions;

final class ServerOptionsTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $options = ServerOptions::default();

        static::assertFalse($options->noDelay);
        static::assertSame(ServerOptions::DEFAULT_IDLE_CONNECTIONS, $options->idleConnections);
        static::assertEquals(SocketOptions::default(), $options->socketOptions);
    }
    public function testNoDelay(): void
    {
        $options = ServerOptions::default();

        static::assertFalse($options->noDelay);

        $options = $options->withNoDelay();

        static::assertTrue($options->noDelay);

        $options = $options->withNoDelay(false);

        static::assertFalse($options->noDelay);

        $options = $options->withNoDelay(true);

        static::assertTrue($options->noDelay);
    }

    public function testIdleConnections(): void
    {
        $options = ServerOptions::create(idle_connections: 10);

        static::assertSame(10, $options->idleConnections);

        $options = $options->withIdleConnections(20);

        static::assertSame(20, $options->idleConnections);
    }

    public function testSocketOptions(): void
    {
        $options = ServerOptions::default();

        static::assertEquals(SocketOptions::default(), $options->socketOptions);

        $socketOptions = SocketOptions::default()->withBroadcast();

        $options = $options->withSocketOptions($socketOptions);

        static::assertSame($socketOptions, $options->socketOptions);
    }
}
