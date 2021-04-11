<?php

declare(strict_types=1);

namespace Psl\Tests\Shell\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Shell\Exception;

final class FailedExecutionExceptionTest extends TestCase
{
    public function testMethods(): void
    {
        $exception = new Exception\FailedExecutionException('foo', 'bar', 'baz', 4);

        static::assertSame('Shell command "foo" returned an exit code of "4".', $exception->getMessage());
        static::assertSame('foo', $exception->getCommand());
        static::assertSame('bar', $exception->getOutput());
        static::assertSame('baz', $exception->getErrorOutput());
        static::assertSame(4, $exception->getCode());
    }
}
