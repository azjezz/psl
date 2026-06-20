<?php

declare(strict_types=1);

namespace Psl\TCP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;
use Psl\TCP;

final class ConnectConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new TCP\ConnectConfiguration();

        static::assertFalse($config->noDelay);
        static::assertNull($config->bindTo);
    }

    public function testDefaultMethod(): void
    {
        $config = TCP\ConnectConfiguration::default();

        static::assertFalse($config->noDelay);
    }

    public function testWithNoDelay(): void
    {
        $config = new TCP\ConnectConfiguration();
        $new = $config->withNoDelay(true);

        static::assertFalse($config->noDelay);
        static::assertTrue($new->noDelay);
    }

    public function testWithBindTo(): void
    {
        $config = new TCP\ConnectConfiguration();
        $new = $config->withBindTo('192.168.1.1:0');

        static::assertNull($config->bindTo);
        static::assertSame('192.168.1.1:0', $new->bindTo);
    }

    public function testWithBindToNull(): void
    {
        $config = new TCP\ConnectConfiguration(bindTo: '192.168.1.1:0');
        $new = $config->withBindTo(null);

        static::assertSame('192.168.1.1:0', $config->bindTo);
        static::assertNull($new->bindTo);
    }

    public function testWithNoDelayPreservesBindTo(): void
    {
        $config = new TCP\ConnectConfiguration(bindTo: '10.0.0.1:0');
        $new = $config->withNoDelay(true);

        static::assertSame('10.0.0.1:0', $new->bindTo);
        static::assertTrue($new->noDelay);
    }

    public function testWithBindToPreservesNoDelay(): void
    {
        $config = new TCP\ConnectConfiguration(noDelay: true);
        $new = $config->withBindTo('10.0.0.1:0');

        static::assertTrue($new->noDelay);
        static::assertSame('10.0.0.1:0', $new->bindTo);
    }

    public function testBindToIsUsedByConnect(): void
    {
        $listener = TCP\listen('127.0.0.1', 0);
        $port = $listener->getLocalAddress()->port;

        $future = Async\run::<void>(static function () use ($listener): void {
            $conn = $listener->accept(new Async\TimeoutCancellationToken(DateTime\Duration::seconds(5)));
            $conn->close();
        });

        $stream = TCP\connect('127.0.0.1', $port, new TCP\ConnectConfiguration(bindTo: '127.0.0.1:0'));
        $local = $stream->getLocalAddress();

        static::assertSame('127.0.0.1', $local->host);
        static::assertGreaterThan(0, $local->port);

        $stream->close();
        $listener->close();
        $future->await();
    }

    public function testImmutability(): void
    {
        $original = new TCP\ConnectConfiguration();
        $modified = $original->withNoDelay(true);

        static::assertFalse($original->noDelay);
        static::assertTrue($modified->noDelay);
        static::assertNotSame($original, $modified);
    }
}
