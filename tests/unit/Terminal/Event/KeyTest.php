<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Event;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Event\Key;

final class KeyTest extends TestCase
{
    public function testCharCreation(): void
    {
        $key = Key::char('a');

        static::assertSame('a', $key->char);
        static::assertSame('a', $key->name);
    }

    public function testNamedCreation(): void
    {
        $key = Key::named('ctrl+c');

        static::assertNull($key->char);
        static::assertSame('ctrl+c', $key->name);
    }

    public function testIsMatchesExact(): void
    {
        $key = Key::named('ctrl+c');

        static::assertTrue($key->is('ctrl+c'));
        static::assertFalse($key->is('ctrl+x'));
    }

    public function testIsCaseInsensitive(): void
    {
        $key = Key::named('Enter');

        static::assertTrue($key->is('enter'));
        static::assertTrue($key->is('ENTER'));
    }

    public function testIsForNamedKeys(): void
    {
        static::assertTrue(Key::named('enter')->is('enter'));
        static::assertTrue(Key::named('backspace')->is('backspace'));
        static::assertTrue(Key::named('tab')->is('tab'));
        static::assertTrue(Key::named('escape')->is('escape'));
        static::assertTrue(Key::named('page_up')->is('page_up'));
        static::assertTrue(Key::named('page_down')->is('page_down'));
        static::assertTrue(Key::named('ctrl+up')->is('ctrl+up'));
        static::assertTrue(Key::named('ctrl+down')->is('ctrl+down'));
    }

    public function testCharProperty(): void
    {
        $key = Key::char('x');
        static::assertSame('x', $key->char);

        $key = Key::named('enter');
        static::assertNull($key->char);
    }
}
