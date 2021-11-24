<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\TCP\ServerOptions;

final class ServerOptionsTest extends TestCase
{
    public function testOptions(): void
    {
        $options = ServerOptions::create();

        static::assertFalse($options->noDelay);

        $options = $options->withNoDelay();

        static::assertTrue($options->noDelay);

        $options = $options->withNoDelay(false);

        static::assertFalse($options->noDelay);
    }
}
