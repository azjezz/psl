<?php

declare(strict_types=1);

namespace Psl\UDP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\UDP;

final class BindConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new UDP\BindConfiguration();

        static::assertFalse($config->reuseAddress);
        static::assertFalse($config->reusePort);
        static::assertFalse($config->broadcast);
    }

    public function testDefaultMethod(): void
    {
        $config = UDP\BindConfiguration::default();

        static::assertFalse($config->reuseAddress);
        static::assertFalse($config->reusePort);
        static::assertFalse($config->broadcast);
    }

    public function testWithReuseAddress(): void
    {
        $config = new UDP\BindConfiguration();
        $new = $config->withReuseAddress(true);

        static::assertFalse($config->reuseAddress);
        static::assertTrue($new->reuseAddress);
    }

    public function testWithReusePort(): void
    {
        $config = new UDP\BindConfiguration();
        $new = $config->withReusePort(true);

        static::assertFalse($config->reusePort);
        static::assertTrue($new->reusePort);
    }

    public function testWithBroadcast(): void
    {
        $config = new UDP\BindConfiguration();
        $new = $config->withBroadcast(true);

        static::assertFalse($config->broadcast);
        static::assertTrue($new->broadcast);
    }

    public function testChaining(): void
    {
        $config = UDP\BindConfiguration::default()->withReuseAddress(true)->withReusePort(true)->withBroadcast(true);

        static::assertTrue($config->reuseAddress);
        static::assertTrue($config->reusePort);
        static::assertTrue($config->broadcast);
    }

    public function testImmutability(): void
    {
        $original = new UDP\BindConfiguration();
        $modified = $original->withBroadcast(true);

        static::assertFalse($original->broadcast);
        static::assertTrue($modified->broadcast);
        static::assertNotSame($original, $modified);
    }
}
