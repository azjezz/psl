<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ConcatTest extends TestCase
{
    public function testConcat(): void
    {
        static::assertSame('', Str\concat(''));
        static::assertSame('abc', Str\concat('a', 'b', 'c'));
        static::assertSame('مرحبا بكم', Str\concat(...['م', 'ر', 'ح', 'ب', 'ا', ' ', 'ب', 'ك', 'م']));
    }
}
