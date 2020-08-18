<?php

declare(strict_types=1);

namespace Psl\Tests\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Exception\InvariantViolationException;

class RemoveVarTest extends TestCase
{
    /**
     * @backupGlobals
     */
    public function testRemoveVar(): void
    {
        self::assertNull(Env\get_var('FOO'));

        Env\set_var('FOO', 'BAR');

        self::assertSame('BAR', Env\get_var('FOO'));

        Env\remove_var('FOO');

        self::assertNull(Env\get_var('FOO'));
    }

    public function testRemoveVarThrowsIfTheKeyIsInvalid(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\remove_var('a=b');
    }

    public function testRemoveVarThrowsIfTheKeyIsEmpty(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\remove_var('');
    }

    public function testRemoveVarThrowsIfTheKeyContainsNUL(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\remove_var("\0");
    }
}
