<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Exception\InvariantViolationException;

final class RemoveVarTest extends TestCase
{
    /**
     * @backupGlobals
     */
    public function testRemoveVar(): void
    {
        static::assertNull(Env\get_var('FOO'));

        Env\set_var('FOO', 'BAR');

        static::assertSame('BAR', Env\get_var('FOO'));

        Env\remove_var('FOO');

        static::assertNull(Env\get_var('FOO'));
    }

    public function testRemoveVarThrowsIfTheKeyIsInvalid(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\remove_var('a=b');
    }

    public function testRemoveVarThrowsIfTheKeyContainsNUL(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\remove_var("\0");
    }
}
