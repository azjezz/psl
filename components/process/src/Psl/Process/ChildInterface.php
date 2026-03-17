<?php

declare(strict_types=1);

namespace Psl\Process;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;

interface ChildInterface
{
    /**
     * Returns the OS-assigned process identifier.
     */
    public function getProcessId(): int;

    /**
     * Returns whether the process is still running.
     */
    public function isRunning(): bool;

    /**
     * Returns the stdin handle for writing to the process.
     *
     * @throws Exception\StreamUnavailableException If stdin was not configured as piped.
     */
    public function getStdin(): IO\WriteHandleInterface&IO\CloseHandleInterface&IO\StreamHandleInterface;

    /**
     * Returns the stdout handle for reading from the process.
     *
     * @throws Exception\StreamUnavailableException If stdout was not configured as piped.
     */
    public function getStdout(): IO\ReadHandleInterface&IO\CloseHandleInterface&IO\StreamHandleInterface;

    /**
     * Returns the stderr handle for reading from the process.
     *
     * @throws Exception\StreamUnavailableException If stderr was not configured as piped.
     */
    public function getStderr(): IO\ReadHandleInterface&IO\CloseHandleInterface&IO\StreamHandleInterface;

    /**
     * Sends a signal to the process.
     *
     * @throws Exception\RuntimeException If the signal could not be sent.
     */
    public function signal(Signal $signal): void;

    /**
     * Sends SIGKILL to forcefully terminate the process.
     *
     * @throws Exception\RuntimeException If the signal could not be sent.
     */
    public function kill(): void;

    /**
     * Waits for the process to exit, returning its exit status.
     *
     * If stdout/stderr are piped, their handles are closed before waiting
     * to prevent deadlocks. Use {@see waitWithOutput()} to collect output.
     *
     * @throws CancelledException If the operation is cancelled.
     */
    public function wait(CancellationTokenInterface $cancellation = new NullCancellationToken()): ExitStatus;

    /**
     * Waits for the process to exit, collecting all stdout and stderr output.
     *
     * This method reads stdout and stderr concurrently to avoid deadlocks,
     * then waits for the process to exit.
     *
     * @throws CancelledException If the operation is cancelled.
     */
    public function waitWithOutput(CancellationTokenInterface $cancellation = new NullCancellationToken()): Output;

    /**
     * Attempts to collect the exit status of the process without blocking.
     *
     * Returns null if the process is still running.
     */
    public function tryWait(): null|ExitStatus;
}
