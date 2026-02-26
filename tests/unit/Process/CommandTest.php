<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Process;

use PHPUnit\Framework\TestCase;
use Psl\Process\Command;
use Psl\Process\Stdio;

final class CommandTest extends TestCase
{
    public function testCreate(): void
    {
        $command = Command::create('git');

        static::assertSame('git', $command->getProgram());
        static::assertSame([], $command->getArguments());
        static::assertSame([], $command->getEnvironmentVariables());
        static::assertNull($command->getWorkingDirectory());
    }

    public function testShell(): void
    {
        $command = Command::shell('echo hello');

        static::assertSame('echo hello', $command->getProgram());
        static::assertSame([], $command->getArguments());
    }

    public function testWithArgument(): void
    {
        $command = Command::create('git')->withArgument('status');

        static::assertSame(['status'], $command->getArguments());
    }

    public function testWithArgumentIsAdditive(): void
    {
        $command = Command::create('git')->withArgument('log')->withArgument('--oneline');

        static::assertSame(['log', '--oneline'], $command->getArguments());
    }

    public function testWithArguments(): void
    {
        $command = Command::create('git')->withArguments(['log', '--oneline']);

        static::assertSame(['log', '--oneline'], $command->getArguments());
    }

    public function testWithArgumentsIsAdditive(): void
    {
        $command = Command::create('git')->withArgument('log')->withArguments(['--oneline', '-n', '10']);

        static::assertSame(['log', '--oneline', '-n', '10'], $command->getArguments());
    }

    public function testWithEnvironmentVariable(): void
    {
        $command = Command::create('env')->withEnvironmentVariable('FOO', 'bar');

        static::assertSame(['FOO' => 'bar'], $command->getEnvironmentVariables());
    }

    public function testWithEnvironmentVariables(): void
    {
        $command = Command::create('env')->withEnvironmentVariables(['FOO' => 'bar', 'BAZ' => 'qux']);

        static::assertSame(['FOO' => 'bar', 'BAZ' => 'qux'], $command->getEnvironmentVariables());
    }

    public function testWithEnvironmentVariablesMerges(): void
    {
        $command = Command::create('env')
            ->withEnvironmentVariable('FOO', 'bar')
            ->withEnvironmentVariables(['BAZ' => 'qux']);

        static::assertSame(['FOO' => 'bar', 'BAZ' => 'qux'], $command->getEnvironmentVariables());
    }

    public function testWithoutEnvironmentVariable(): void
    {
        $command = Command::create('env')
            ->withEnvironmentVariables(['FOO' => 'bar', 'BAZ' => 'qux'])
            ->withoutEnvironmentVariable('FOO');

        static::assertSame(['BAZ' => 'qux'], $command->getEnvironmentVariables());
    }

    public function testWithClearedEnvironment(): void
    {
        $command = Command::create('env')->withEnvironmentVariables(['FOO' => 'bar'])->withClearedEnvironment();

        static::assertSame([], $command->getEnvironmentVariables());
    }

    public function testWithWorkingDirectory(): void
    {
        $command = Command::create('ls')->withWorkingDirectory('/tmp');

        static::assertSame('/tmp', $command->getWorkingDirectory());
    }

    public function testImmutability(): void
    {
        $original = Command::create('git');
        $modified = $original->withArgument('status');

        static::assertSame([], $original->getArguments());
        static::assertSame(['status'], $modified->getArguments());
    }

    public function testSpawnInvalidWorkingDirectory(): void
    {
        $this->expectException(\Psl\Process\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Working directory does not exist.');

        Command::create(PHP_BINARY)
            ->withArgument('-r')
            ->withArgument('echo "hello";')
            ->withWorkingDirectory('/nonexistent/path/that/does/not/exist')
            ->spawn();
    }

    public function testOutputInvalidWorkingDirectory(): void
    {
        $this->expectException(\Psl\Process\Exception\RuntimeException::class);

        Command::create(PHP_BINARY)
            ->withArgument('-r')
            ->withArgument('echo "hello";')
            ->withWorkingDirectory('/nonexistent/path/that/does/not/exist')
            ->output();
    }

    public function testStatusInvalidWorkingDirectory(): void
    {
        $this->expectException(\Psl\Process\Exception\RuntimeException::class);

        Command::create(PHP_BINARY)
            ->withArgument('-r')
            ->withArgument('echo "hello";')
            ->withWorkingDirectory('/nonexistent/path/that/does/not/exist')
            ->status();
    }

    public function testNullByteInCommand(): void
    {
        $this->expectException(\Psl\Process\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Command line contains NULL bytes.');

        Command::create("test\0command")->spawn();
    }

    public function testWithStdin(): void
    {
        $command = Command::create('cat')->withStdin(Stdio::piped());

        // Verify it doesn't throw — stdin is piped.
        $child = $command->withArgument('-')->spawn();

        $child->getStdin()->writeAll("hello\n");
        $child->getStdin()->close();
        $output = $child->getStdout()->readAll();
        $child->wait();

        static::assertSame("hello\n", $output);
    }

    public function testWithStdout(): void
    {
        $command = Command::create(PHP_BINARY)
            ->withArgument('-r')
            ->withArgument('echo "test";')
            ->withStdout(Stdio::null());

        $child = $command->spawn();

        $this->expectException(\Psl\Process\Exception\StreamUnavailableException::class);

        $child->getStdout();
    }

    public function testWithStderr(): void
    {
        $command = Command::create(PHP_BINARY)
            ->withArgument('-r')
            ->withArgument('echo "test";')
            ->withStderr(Stdio::null());

        $child = $command->spawn();

        $this->expectException(\Psl\Process\Exception\StreamUnavailableException::class);

        $child->getStderr();
    }
}
