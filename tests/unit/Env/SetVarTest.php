<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Exception\InvariantViolationException;

final class SetVarTest extends TestCase
{
    public function testSetVarThrowsIfTheKeyIsInvalid(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\set_var('a=b', 'foo');
    }

    public function testSetVarThrowsIfTheKeyContainsNUL(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable key provided.');

        Env\set_var("\0", 'foo');
    }

    public function testSetVarThrowsIfTheValueContainsNUL(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Invalid environment variable value provided.');

        Env\set_var('foo', "\0");
    }
}
