<?php

declare(strict_types=1);

namespace Psl\Process\Internal;

use Override;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\OS;
use Psl\Process\ChildInterface;
use Psl\Process\Exception;
use Psl\Process\ExitStatus;
use Psl\Process\Output;
use Psl\Process\Signal;
use Revolt\EventLoop;

use function proc_close;
use function proc_get_status;
use function proc_terminate;

/**
 * @internal
 */
final class Child implements ChildInterface
{
    private bool $exited = false;
    private null|ExitStatus $exitStatus = null;

    /**
     * @param resource $process
     */
    public function __construct(
        private mixed $process,
        private null|IO\CloseWriteStreamHandle $stdin,
        private null|IO\CloseReadStreamHandle $stdout,
        private null|IO\CloseReadStreamHandle $stderr,
    ) {}

    #[Override]
    public function getProcessId(): int
    {
        $status = proc_get_status($this->process);

        return (int) $status['pid'];
    }

    #[Override]
    public function isRunning(): bool
    {
        if ($this->exited) {
            return false;
        }

        $status = proc_get_status($this->process);

        return $status['running'];
    }

    #[Override]
    public function getStdin(): IO\WriteHandleInterface&IO\CloseHandleInterface&IO\StreamHandleInterface
    {
        if (null === $this->stdin) {
            throw new Exception\StreamUnavailableException(
                'Stdin is not available. Configure it with Stdio::piped() to access it.',
            );
        }

        return $this->stdin;
    }

    #[Override]
    public function getStdout(): IO\ReadHandleInterface&IO\CloseHandleInterface&IO\StreamHandleInterface
    {
        if (null === $this->stdout) {
            throw new Exception\StreamUnavailableException(
                'Stdout is not available. Configure it with Stdio::piped() to access it.',
            );
        }

        return $this->stdout;
    }

    #[Override]
    public function getStderr(): IO\ReadHandleInterface&IO\CloseHandleInterface&IO\StreamHandleInterface
    {
        if (null === $this->stderr) {
            throw new Exception\StreamUnavailableException(
                'Stderr is not available. Configure it with Stdio::piped() to access it.',
            );
        }

        return $this->stderr;
    }

    #[Override]
    public function signal(Signal $signal): void
    {
        if (!$this->isRunning()) {
            return;
        }

        // @codeCoverageIgnoreStart
        if (OS\is_windows()) {
            // Windows only supports SIGTERM via proc_terminate which calls TerminateProcess.
            proc_terminate($this->process);

            return;
        }

        // @codeCoverageIgnoreEnd

        $result = proc_terminate($this->process, $signal->value);
        if (!$result) {
            throw new Exception\RuntimeException('Failed to send signal to the process.');
        }
    }

    #[Override]
    public function kill(): void
    {
        $this->signal(Signal::Kill);
    }

    #[Override]
    public function wait(null|Duration $timeout = null): ExitStatus
    {
        if (null !== $this->exitStatus) {
            return $this->exitStatus;
        }

        // Close stdin to signal EOF to the child process.
        $this->stdin?->close();
        $this->stdin = null;

        // Close stdout/stderr to avoid deadlock — unread data is discarded.
        $this->stdout?->close();
        $this->stdout = null;
        $this->stderr?->close();
        $this->stderr = null;

        return $this->doWait($timeout);
    }

    #[Override]
    public function waitWithOutput(null|Duration $timeout = null): Output
    {
        if (null !== $this->exitStatus) {
            return new Output($this->exitStatus, '', '');
        }

        // Close stdin to signal EOF to the child process.
        $this->stdin?->close();
        $this->stdin = null;

        $stdoutContent = '';
        $stderrContent = '';

        // Read stdout and stderr concurrently using IO\streaming.
        $handles = [];
        if (null !== $this->stdout) {
            $handles[1] = $this->stdout;
        }

        if (null !== $this->stderr) {
            $handles[2] = $this->stderr;
        }

        if ([] !== $handles) {
            try {
                foreach (IO\streaming($handles, $timeout) as $type => $chunk) {
                    if ('' === $chunk) {
                        continue;
                    }

                    if (1 === $type) {
                        $stdoutContent .= $chunk;
                    } else {
                        $stderrContent .= $chunk;
                    }
                }
            } catch (IO\Exception\TimeoutException $e) {
                // Kill the process on timeout before closing handles.
                $this->kill();
                $this->closeHandles();
                $this->close();

                throw new Exception\TimeoutException('Process timed out while reading output.', 0, $e);
            }
        }

        $this->closeHandles();

        $status = $this->doWait(null);

        return new Output($status, $stdoutContent, $stderrContent);
    }

    #[Override]
    public function tryWait(): null|ExitStatus
    {
        if (null !== $this->exitStatus) {
            return $this->exitStatus;
        }

        if ($this->isRunning()) {
            return null;
        }

        return $this->close();
    }

    private function doWait(null|Duration $timeout): ExitStatus
    {
        if (null !== $this->exitStatus) {
            return $this->exitStatus;
        }

        if (null === $timeout) {
            return $this->close();
        }

        $timeoutSeconds = $timeout->getTotalSeconds();
        $timedOut = false;
        $timeoutWatcher = EventLoop::delay($timeoutSeconds, static function () use (&$timedOut): void {
            $timedOut = true;
        });

        try {
            while ($this->isRunning()) {
                if ($timedOut) {
                    $this->kill();
                    $this->close();

                    throw new Exception\TimeoutException('Process timed out.');
                }

                $suspension = EventLoop::getSuspension();
                EventLoop::defer(static function () use ($suspension): void {
                    $suspension->resume();
                });
                $suspension->suspend();
            }
        } finally {
            EventLoop::cancel($timeoutWatcher);
        }

        return $this->close();
    }

    private function closeHandles(): void
    {
        $this->stdout?->close();
        $this->stdout = null;
        $this->stderr?->close();
        $this->stderr = null;
    }

    private function close(): ExitStatus
    {
        if (null !== $this->exitStatus) {
            return $this->exitStatus;
        }

        $this->exited = true;

        $info = proc_get_status($this->process);
        $code = proc_close($this->process);

        // proc_get_status only returns the real exit code on the first call after exit.
        // After that, it returns -1. proc_close returns the exit code too, but it's
        // shifted on Unix. Use the info exitcode when available.
        $exitCode = -1 !== $info['exitcode'] ? $info['exitcode'] : $code;

        $signaled = $info['signaled'];
        $termSignal = $signaled ? $info['termsig'] : null;

        $this->exitStatus = new ExitStatus($exitCode, $signaled, $termSignal);

        return $this->exitStatus;
    }
}
