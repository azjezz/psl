<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class CompositeExceptionTest extends TestCase
{
    public function testGetReasons(): void
    {
        $reasons = [
            new InvariantViolationException('foo'),
            new InvariantViolationException('bar'),
        ];

        $exception = new Async\Exception\CompositeException($reasons);

        static::assertSame($reasons, $exception->getReasons());
    }
}
