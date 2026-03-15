<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\TCP;

final class ConnectConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new TCP\ConnectConfiguration();

        static::assertFalse($config->noDelay);
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

    public function testImmutability(): void
    {
        $original = new TCP\ConnectConfiguration();
        $modified = $original->withNoDelay(true);

        static::assertFalse($original->noDelay);
        static::assertTrue($modified->noDelay);
        static::assertNotSame($original, $modified);
    }
}
