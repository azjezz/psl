<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class ConcatTest extends TestCase
{
    public function testConcat(): void
    {
        self::assertSame('', Str\concat(''));
        self::assertSame('abc', Str\concat('a', 'b', 'c'));
        self::assertSame('مرحبا بكم', Str\concat(...['م', 'ر', 'ح', 'ب', 'ا', ' ', 'ب', 'ك', 'م']));
    }
}
