<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TCP;

use PHPUnit\Framework\TestCase;
use Psl\TCP\ConnectOptions;

final class ConnectOptionsTest extends TestCase
{
    public function testOptions(): void
    {
        $options = ConnectOptions::create();

        static::assertFalse($options->noDelay);

        $options = $options->withNoDelay();

        static::assertTrue($options->noDelay);

        $options = $options->withNoDelay(false);

        static::assertFalse($options->noDelay);
    }
}
