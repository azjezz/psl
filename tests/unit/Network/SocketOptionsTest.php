<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Network;

use PHPUnit\Framework\TestCase;
use Psl\Network\SocketOptions;

final class SocketOptionsTest extends TestCase
{
    public function testOptions(): void
    {
        $options = SocketOptions::create();

        static::assertFalse($options->addressReuse);
        static::assertFalse($options->portReuse);
        static::assertFalse($options->broadcast);

        $options = $options->withAddressReuse();

        static::assertTrue($options->addressReuse);
        static::assertFalse($options->portReuse);
        static::assertFalse($options->broadcast);

        $options = $options->withPortReuse();

        static::assertTrue($options->addressReuse);
        static::assertTrue($options->portReuse);
        static::assertFalse($options->broadcast);

        $options = $options->withBroadcast();

        static::assertTrue($options->addressReuse);
        static::assertTrue($options->portReuse);
        static::assertTrue($options->broadcast);
    }
}
