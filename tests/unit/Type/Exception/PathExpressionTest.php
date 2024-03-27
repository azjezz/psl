<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Exception;

use PHPUnit\Framework\TestCase;
use Psl\Type\Exception\PathExpression;

final class PathExpressionTest extends TestCase
{
    public function testPath(): void
    {
        static::assertSame('foo', PathExpression::path('foo'));
        static::assertSame('1', PathExpression::path(1));
        static::assertSame('1.1', PathExpression::path(1.1));
        static::assertSame('true', PathExpression::path(true));
        static::assertSame('false', PathExpression::path(false));
        static::assertSame('null', PathExpression::path(null));
        static::assertSame('array', PathExpression::path([]));
        static::assertSame('class@anonymous', PathExpression::path(new class () {
        }));
    }

    public function testExpression(): void
    {
        static::assertSame('expr(foo)', PathExpression::expression('expr(%s)', 'foo'));
    }

    public function testIteratorKey(): void
    {
        static::assertSame('key(foo)', PathExpression::iteratorKey('foo'));
        static::assertSame('key(null)', PathExpression::iteratorKey(null));
    }

    public function testIteratorError(): void
    {
        static::assertSame('first()', PathExpression::iteratorError(null));
        static::assertSame('foo.next()', PathExpression::iteratorError('foo'));
    }
}
