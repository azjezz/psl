<?php

declare(strict_types=1);

namespace Psl\Terminal\Internal;

use Psl\Process;
use Psl\Str;

use function count;
use function trim;

use const PHP_OS_FAMILY;

/**
 * Query terminal dimensions.
 *
 * @internal
 */
final class TerminalSize
{
    private function __construct() {}

    /**
     * Returns the current terminal size as [columns, rows].
     *
     * @return array{int, int}
     *
     * @throws Exception\RuntimeException If unable to determine terminal size.
     */
    public static function get(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return self::getWindows();
        }

        return self::getUnix();
    }

    /**
     * @return array{int, int}
     */
    private static function getUnix(): array
    {
        $result = self::tryStty();
        if ($result !== null) {
            return $result;
        }

        $result = self::tryTput();
        if ($result !== null) {
            return $result;
        }

        return [80, 24];
    }

    /**
     * Try to get terminal size via stty.
     *
     * @return null|array{int, int}
     */
    private static function tryStty(): null|array
    {
        try {
            $child = Process\Command::create('stty')
                ->withArguments(['size'])
                ->withStdin(Process\Stdio::tty())
                ->withStdout(Process\Stdio::piped())
                ->withStderr(Process\Stdio::piped())
                ->spawn();

            $output = $child->waitWithOutput();

            if ($output->status->isSuccessful()) {
                $parts = Str\split(Str\trim($output->stdout), ' ');
                if (count($parts) === 2) {
                    $rows = (int) $parts[0];
                    $cols = (int) $parts[1];
                    if ($rows > 0 && $cols > 0) {
                        return [$cols, $rows];
                    }
                }
            }
        } catch (Process\Exception\ExceptionInterface) {
            return null;
        }

        return null;
    }

    /**
     * Try to get terminal size via tput.
     *
     * @return null|array{int, int}
     */
    private static function tryTput(): null|array
    {
        try {
            $colsOutput = Process\Command::create('tput')
                ->withArguments(['cols'])
                ->withStdout(Process\Stdio::piped())
                ->withStderr(Process\Stdio::piped())
                ->spawn()
                ->waitWithOutput();

            $rowsOutput = Process\Command::create('tput')
                ->withArguments(['lines'])
                ->withStdout(Process\Stdio::piped())
                ->withStderr(Process\Stdio::piped())
                ->spawn()
                ->waitWithOutput();

            if ($colsOutput->status->isSuccessful() && $rowsOutput->status->isSuccessful()) {
                $cols = (int) trim($colsOutput->stdout);
                $rows = (int) trim($rowsOutput->stdout);
                if ($rows > 0 && $cols > 0) {
                    return [$cols, $rows];
                }
            }
        } catch (Process\Exception\ExceptionInterface) {
            return null;
        }

        return null;
    }

    /**
     * @return array{int, int}
     */
    private static function getWindows(): array
    {
        try {
            $output = Process\Command::create('powershell')
                ->withArguments([
                    '-NoProfile',
                    '-Command',
                    '[Console]::WindowWidth.ToString() + " " + [Console]::WindowHeight.ToString()',
                ])
                ->withStdout(Process\Stdio::piped())
                ->withStderr(Process\Stdio::piped())
                ->spawn()
                ->waitWithOutput();

            if ($output->status->isSuccessful()) {
                $parts = Str\split(Str\trim($output->stdout), ' ');
                if (count($parts) === 2) {
                    $cols = (int) $parts[0];
                    $rows = (int) $parts[1];
                    if ($cols > 0 && $rows > 0) {
                        return [$cols, $rows];
                    }
                }
            }
        } catch (Process\Exception\ExceptionInterface) {
            // @mago-expect lint:no-empty-catch-clause - best-effort fallback
        }

        return [80, 24];
    }
}
