<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Shell;

use PHPUnit\Framework\TestCase;
use Psl\DateTime;
use Psl\DateTime\Duration;
use Psl\Env;
use Psl\OS;
use Psl\SecureRandom;
use Psl\Shell;

final class ExecuteTest extends TestCase
{
    public function testExecute(): void
    {
        static::assertSame('Hello, World!', Shell\execute(PHP_BINARY, ['-r', 'echo "Hello, World!";']));
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
        if (OS\is_windows()) {
            static::markTestSkipped('Test can only be executed under *nix OS.');
        }

        $this->expectException(Shell\Exception\PossibleAttackException::class);

        Shell\execute('php', ["\0"]);
    }

    public function testEnvironmentIsPassedDownToTheProcess(): void
    {
        static::assertSame('BAR', Shell\execute(PHP_BINARY, ['-r', 'echo getenv("FOO");'], null, ['FOO' => 'BAR']));
    }

    public function testCurrentEnvironmentVariablesArePassedDownToTheProcess(): void
    {
        try {
            Env\set_var('FOO', 'BAR');

            static::assertSame('BAR', Shell\execute(PHP_BINARY, ['-r', 'echo getenv("FOO");']));
        } finally {
            Env\remove_var('FOO');
        }
    }

    public function testWorkingDirectoryIsUsed(): void
    {
        $temp = Env\temp_dir();
        $result = Shell\execute(PHP_BINARY, ['-r', 'echo getcwd();'], $temp);

        static::assertStringEndsWith($temp, $result);
    }

    public function testCurrentDirectoryIsUsedByDefault(): void
    {
        $dir = Env\current_dir();

        static::assertSame($dir, Shell\execute(PHP_BINARY, ['-r', 'echo getcwd();']));
    }

    public function testItThrowsWhenWorkingDirectoryDoesntExist(): void
    {
        $dir = Env\current_dir() . DIRECTORY_SEPARATOR . SecureRandom\string(6);

        $this->expectException(Shell\Exception\RuntimeException::class);
        $this->expectExceptionMessage('$working_directory does not exist.');

        Shell\execute(PHP_BINARY, ['-r', 'echo getcwd();'], $dir);
    }

    public function testErrorOutputIsDiscarded(): void
    {
        $result = Shell\execute(PHP_BINARY, ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");']);

        static::assertSame('hello', $result);

        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");'],
            error_output_behavior: Shell\ErrorOutputBehavior::default(),
        );

        static::assertSame('hello', $result);
    }

    public function testErrorOutputIsAppended(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Append,
        );

        static::assertSame('hello world', $result);
    }

    public function testErrorOutputIsPrepended(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Prepend,
        );

        static::assertSame(' worldhello', $result);
    }

    public function testErrorOutputIsReplacingStandardOutput(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Replace,
        );

        static::assertSame(' world', $result);
    }

    public function testErrorOutputIsPacked(): void
    {
        $result = Shell\execute(
            PHP_BINARY,
            ['-r', 'fwrite(STDOUT, "hello"); fwrite(STDERR, " world");'],
            error_output_behavior: Shell\ErrorOutputBehavior::Packed,
        );

        [$stdout, $stderr] = Shell\unpack($result);

        static::assertSame('hello', $stdout);
        static::assertSame(' world', $stderr);
    }

    public function testTimeoutDoesNotAffectFastCommands(): void
    {
        $result = Shell\execute(PHP_BINARY, ['-r', 'echo "hello";'], timeout: Duration::seconds(5));

        static::assertSame('hello', $result);
    }

    public function testTimeoutException(): void
    {
        $start = DateTime\Timestamp::monotonic();

        try {
            Shell\execute(PHP_BINARY, ['-r', 'sleep(10);'], timeout: Duration::seconds(2));
        } catch (Shell\Exception\TimeoutException $_) {
            $elapsed = DateTime\Timestamp::monotonic()->since($start);

            static::assertLessThan(
                3.0,
                $elapsed->getTotalSeconds(),
                'Process was not killed after timeout — proc_close() likely blocked.',
            );

            return;
        }

        static::fail('Expected timeout exception');
    }
}
