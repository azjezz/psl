<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Type\Internal\PathExpression;

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
        static::assertSame(
            'class@anonymous',
            PathExpression::path(new class() {
            }),
        );
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

    public function testCoerceInput(): void
    {
        static::assertSame('coerce_input(string): string', PathExpression::coerceInput('foo', 'string'));
        static::assertSame('coerce_input(array): string', PathExpression::coerceInput([], 'string'));
    }

    public function testConvert(): void
    {
        static::assertSame('convert(string): string', PathExpression::convert('foo', 'string'));
        static::assertSame('convert(array): string', PathExpression::convert([], 'string'));
    }

    public function testCoerceOutput(): void
    {
        static::assertSame('coerce_output(string): string', PathExpression::coerceOutput('foo', 'string'));
        static::assertSame('coerce_output(array): string', PathExpression::coerceOutput([], 'string'));
    }
}
