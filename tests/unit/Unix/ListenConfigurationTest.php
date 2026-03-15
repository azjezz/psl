<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Unix;

use PHPUnit\Framework\TestCase;
use Psl\Unix;

final class ListenConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new Unix\ListenConfiguration();

        static::assertSame(512, $config->backlog);
        static::assertSame(256, $config->idleConnections);
    }

    public function testDefaultMethod(): void
    {
        $config = Unix\ListenConfiguration::default();

        static::assertSame(512, $config->backlog);
        static::assertSame(256, $config->idleConnections);
    }

    public function testWithBacklog(): void
    {
        $config = new Unix\ListenConfiguration();
        $new = $config->withBacklog(1024);

        static::assertSame(512, $config->backlog);
        static::assertSame(1024, $new->backlog);
    }

    public function testWithIdleConnections(): void
    {
        $config = new Unix\ListenConfiguration();
        $new = $config->withIdleConnections(64);

        static::assertSame(256, $config->idleConnections);
        static::assertSame(64, $new->idleConnections);
    }

    public function testChaining(): void
    {
        $config = Unix\ListenConfiguration::default()->withBacklog(2048)->withIdleConnections(128);

        static::assertSame(2048, $config->backlog);
        static::assertSame(128, $config->idleConnections);
    }
}
