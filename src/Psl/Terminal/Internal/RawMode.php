<?php

declare(strict_types=1);

namespace Psl\Terminal\Internal;

use Psl\OS;
use Psl\Process;
use Psl\Str;
use Psl\Terminal\Exception;

/**
 * Manages terminal raw mode (stty raw/cooked).
 *
 * @internal
 */
final class RawMode
{
    private null|string $savedState = null;

    /**
     * Enable raw mode and save the current terminal state.
     *
     * @throws Exception\RuntimeException If unable to enable raw mode.
     */
    public function enable(): void
    {
        if (Os\is_windows()) {
            $this->enableWindows();
            return;
        }

        $this->enableUnix();
    }

    /**
     * Restore the terminal to its previous state.
     */
    public function restore(): void
    {
        if ($this->savedState === null) {
            return;
        }

        if (Os\is_windows()) {
            $this->restoreWindows();
            return;
        }

        $this->restoreUnix();
    }

    /**
     * Run stty with /dev/tty as stdin so it can access the real terminal.
     *
     * @param list<string> $arguments
     */
    private static function stty(array $arguments): string
    {
        $child = Process\Command::create('stty')
            ->withArguments($arguments)
            ->withStdin(Process\Stdio::tty())
            ->withStdout(Process\Stdio::piped())
            ->withStderr(Process\Stdio::piped())
            ->spawn();

        $output = $child->waitWithOutput();

        if (!$output->status->isSuccessful()) {
            throw new Exception\RuntimeException(
                'stty command failed with exit code ' . $output->status->getCode() . ': ' . Str\trim($output->stderr),
            );
        }

        return $output->stdout;
    }

    /**
     * Try to run an stty command, returning true on success.
     */
    private static function tryStty(string $arg): bool
    {
        try {
            self::stty([$arg]);
            return true;
        } catch (Process\Exception\ExceptionInterface|Exception\RuntimeException) {
            return false;
        }
    }

    /**
     * Run the bundled PowerShell script to manage console mode on Windows.
     *
     * @param list<string> $arguments
     */
    private static function powershell(array $arguments): string
    {
        $script = __DIR__ . '/console_mode.ps1';

        $child = Process\Command::create('powershell')
            ->withArguments(['-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $script, ...$arguments])
            ->withStdout(Process\Stdio::piped())
            ->withStderr(Process\Stdio::piped())
            ->spawn();

        $output = $child->waitWithOutput();

        if (!$output->status->isSuccessful()) {
            throw new Exception\RuntimeException('PowerShell console_mode.ps1 failed: ' . Str\trim($output->stderr));
        }

        return $output->stdout;
    }

    private function enableUnix(): void
    {
        try {
            $this->savedState = Str\trim(self::stty(['-g']));
        } catch (Process\Exception\ExceptionInterface|Exception\RuntimeException $e) {
            throw new Exception\RuntimeException('Failed to save terminal state: ' . $e->getMessage(), previous: $e);
        }

        try {
            self::stty(['raw', '-echo']);
        } catch (Process\Exception\ExceptionInterface|Exception\RuntimeException $e) {
            // Try to restore on failure
            $this->restoreUnix();
            throw new Exception\RuntimeException('Failed to enable raw mode: ' . $e->getMessage(), previous: $e);
        }
    }

    private function restoreUnix(): void
    {
        if ($this->savedState === null) {
            return;
        }

        $restored = self::tryStty($this->savedState);
        if (!$restored) {
            self::tryStty('sane');
        }

        $this->savedState = null;
    }

    private function enableWindows(): void
    {
        try {
            $this->savedState = Str\trim(self::powershell([]));
        } catch (Process\Exception\ExceptionInterface|Exception\RuntimeException $e) {
            throw new Exception\RuntimeException(
                'Failed to enable raw mode on Windows: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    private function restoreWindows(): void
    {
        if ($this->savedState === null) {
            return;
        }

        try {
            self::powershell(['-Restore', $this->savedState]);
        } catch (Process\Exception\ExceptionInterface|Exception\RuntimeException) {
            // @mago-expect lint:no-empty-catch-clause - best-effort restore
        }

        $this->savedState = null;
    }
}
