<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Psl\H2\Exception\ConnectionException;

final class ConnectionExceptionTest extends TestCase
{
    public function testForConnectionClosed(): void
    {
        $exception = ConnectionException::forConnectionClosed();

        static::assertSame('HTTP/2 connection is closed.', $exception->getMessage());
        static::assertNull($exception->getPrevious());
    }
}
