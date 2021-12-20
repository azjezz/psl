<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Env;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\Exception\InvariantViolationException;

final class CurrentDirTest extends TestCase
{
    public function testCurrentDir(): void
    {
        $cwd = Env\current_dir();
        static::assertSame(getcwd(), $cwd);

        Env\set_current_dir(__DIR__);

        static::assertSame(__DIR__, Env\current_dir());

        Env\set_current_dir($cwd);
    }

    public function testChangeCurrentDirectoryThrowsIfUnableTo(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Unable to change directory');

        Env\set_current_dir('/foo/bar/baz/qux');
    }
}
