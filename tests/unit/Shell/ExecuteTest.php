<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Shell;

use PHPUnit\Framework\TestCase;
use Psl\Env;
use Psl\SecureRandom;
use Psl\Shell;

use const PHP_OS_FAMILY;

final class ExecuteTest extends TestCase
{
    public function testExecute(): void
    {
        static::assertSame(
            "Hello, World!",
            Shell\execute(PHP_BINARY, ['-r', 'echo "Hello, World!";'])
        );
    }

    public function testFailedExecution(): void
    {
        try {
            Shell\execute('php', ['-r', 'write("Hello, World!");']);
        } catch (Shell\Exception\FailedExecutionException $exception) {
            static::assertSame(255, $exception->getCode());
            static::assertStringContainsString('Call to undefined function write()', $exception->getErrorOutput());
            static::assertStringContainsString('php', $exception->getCommand());
        }
    }

    public function testItThrowsForNULLByte(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Test can only be executed under *nix OS.');
        }

        $this->expectException(Shell\Exception\PossibleAttackException::class);

        Shell\execute('php', ["\0"]);
    }

    public function testEnvironmentIsPassedDownToTheProcess(): void
    {
        static::assertSame(
            'BAR',
            Shell\execute(PHP_BINARY, ['-r', 'echo getenv("FOO");'], null, ['FOO' => 'BAR'])
        );
    }

    public function testCurrentEnvironmentVariablesArePassedDownToTheProcess(): void
    {
        try {
            Env\set_var('FOO', 'BAR');

            static::assertSame(
                'BAR',
                Shell\execute(PHP_BINARY, ['-r', 'echo getenv("FOO");'])
            );
        } finally {
            Env\remove_var('FOO');
        }
    }

    public function testWorkingDirectoryIsUsed(): void
    {
        if ('Darwin' === PHP_OS_FAMILY || PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped();
        }

        $temp = Env\temp_dir();

        static::assertSame(
            $temp,
            Shell\execute(PHP_BINARY, ['-r', 'echo getcwd();'], $temp)
        );
    }

    public function testCurrentDirectoryIsUsedByDefault(): void
    {
        $dir = Env\current_dir();

        static::assertSame(
            $dir,
            Shell\execute(PHP_BINARY, ['-r', 'echo getcwd();'])
        );
    }

    public function testItThrowsWhenWorkingDirectoryDoesntExist(): void
    {
        $dir = Env\current_dir() . DIRECTORY_SEPARATOR . SecureRandom\string(6);

        $this->expectException(Shell\Exception\RuntimeException::class);
        $this->expectExceptionMessage('$working_directory does not exist.');

        Shell\execute(PHP_BINARY, ['-r', 'echo getcwd();'], $dir);
    }
}
