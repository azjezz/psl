<?php

declare(strict_types=1);

namespace Psl\HPACK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HPACK\Header;

final class HeaderTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $header = new Header(':method', 'GET');

        static::assertSame(':method', $header->name);
        static::assertSame('GET', $header->value);
        static::assertFalse($header->sensitive);
    }

    public function testSensitiveConstruction(): void
    {
        $header = new Header('authorization', 'Bearer token', true);

        static::assertSame('authorization', $header->name);
        static::assertSame('Bearer token', $header->value);
        static::assertTrue($header->sensitive);
    }

    public function testEmptyValues(): void
    {
        $header = new Header('x', '');

        static::assertSame('x', $header->name);
        static::assertSame('', $header->value);
        static::assertFalse($header->sensitive);
    }
}
