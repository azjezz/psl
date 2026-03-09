<?php

declare(strict_types=1);

namespace Psl\Shell;

use Psl\DateTime\Duration;
use Psl\Process;
use Psl\Str;

use function pack;
use function strlen;

/**
 * Execute an external program.
 *
 * @param non-empty-string $command The command to execute.
 * @param list<string> $arguments The command arguments listed as separate entries.
 * @param null|non-empty-string $working_directory The initial working directory for the command.
 *                                                 This must be an absolute directory path, or null if you want to
 *                                                 use the default value ( the current directory )
 * @param array<string, string> $environment A dict with the environment variables for the command that
 *                                           will be run.
 *
 * @psalm-taint-sink shell $command
 *
 * @throws Exception\FailedExecutionException In case the command resulted in an exit code other than 0.
 * @throws Exception\PossibleAttackException In case the command being run is suspicious ( e.g: contains NULL byte ).
 * @throws Exception\RuntimeException In case $working_directory doesn't exist, or unable to create a new process.
 * @throws Exception\TimeoutException If $timeout is reached before being able to read the process stream.
 */
function execute(
    string $command,
    array $arguments = [],
    null|string $working_directory = null,
    array $environment = [],
    ErrorOutputBehavior $error_output_behavior = ErrorOutputBehavior::Discard,
    null|Duration $timeout = null,
): string {
    if (Str\contains($command, "\0")) {
        throw new Exception\PossibleAttackException('NULL byte detected.');
    }

    foreach ($arguments as $argument) {
        if (Str\contains($argument, "\0")) {
            throw new Exception\PossibleAttackException('NULL byte detected.');
        }
    }

    $cmd = Process\Command::create($command)->withArguments($arguments)->withEnvironmentVariables($environment);

    if (null !== $working_directory) {
        $cmd = $cmd->withWorkingDirectory($working_directory);
    }

    try {
        $output = $cmd->output($timeout);
    } catch (Process\Exception\TimeoutException $e) {
        throw new Exception\TimeoutException('reached timeout while the process output is still not readable.', 0, $e);
    } catch (Process\Exception\RuntimeException $e) {
        throw new Exception\RuntimeException($e->getMessage(), 0, $e);
    }

    if (!$output->status->isSuccessful()) {
        $commandline = Str\join([$command, ...$arguments], ' ');

        throw new Exception\FailedExecutionException(
            $commandline,
            $output->stdout,
            $output->stderr,
            $output->status->getCode(),
        );
    }

    if (ErrorOutputBehavior::Packed === $error_output_behavior) {
        $result = '';
        if ('' !== $output->stdout) {
            $result .= pack('C1N1', 1, strlen($output->stdout)) . $output->stdout;
        }

        if ('' !== $output->stderr) {
            $result .= pack('C1N1', 2, strlen($output->stderr)) . $output->stderr;
        }

        return $result;
    }

    return match ($error_output_behavior) {
        ErrorOutputBehavior::Prepend => $output->stderr . $output->stdout,
        ErrorOutputBehavior::Append => $output->stdout . $output->stderr,
        ErrorOutputBehavior::Replace => $output->stderr,
        ErrorOutputBehavior::Discard => $output->stdout,
    };
}
