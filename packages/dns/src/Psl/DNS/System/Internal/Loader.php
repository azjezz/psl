<?php

declare(strict_types=1);

namespace Psl\DNS\System\Internal;

use Psl\DNS\Exception;
use Psl\DNS\System\Settings;
use Psl\Process;

use const PHP_OS_FAMILY;

/**
 * Loads system DNS configuration from OS-specific sources.
 *
 * All methods use {@see Process\Command} for non-blocking execution.
 *
 * @internal
 */
final class Loader
{
    /**
     * Load the system DNS configuration for the current OS.
     *
     * @throws Exception\SystemException If the system configuration cannot be loaded.
     */
    public static function load(): Settings
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => self::loadWindows(),
            'Darwin' => self::loadDarwin(),
            default => self::loadLinux(),
        };
    }

    /**
     * Load DNS configuration from /etc/resolv.conf via `cat`.
     *
     * Used on Linux, FreeBSD, and other Unix-like systems.
     *
     * @throws Exception\SystemException If the command fails.
     */
    public static function loadLinux(): Settings
    {
        $output = self::execute('cat', ['/etc/resolv.conf']);

        return ResolvConfParser::parse($output);
    }

    /**
     * Load DNS configuration from `scutil --dns`.
     *
     * @throws Exception\SystemException If the command fails.
     */
    public static function loadDarwin(): Settings
    {
        $output = self::execute('scutil', ['--dns']);

        return ScutilDnsParser::parse($output);
    }

    /**
     * Load DNS configuration from `ipconfig /all`.
     *
     * @throws Exception\SystemException If the command fails.
     */
    public static function loadWindows(): Settings
    {
        $output = self::execute('ipconfig', ['/all']);

        return IpconfigParser::parse($output);
    }

    /**
     * @param non-empty-string $program
     * @param list<string> $arguments
     *
     * @throws Exception\SystemException If the command fails.
     */
    private static function execute(string $program, array $arguments): string
    {
        try {
            $result = Process\Command::create($program)->withArguments($arguments)->output();
        } catch (Process\Exception\ExceptionInterface $e) {
            throw Exception\SystemException::forCommandFailed($program, $e->getMessage(), $e);
        }

        if (!$result->status->isSuccessful()) {
            throw Exception\SystemException::forCommandFailed(
                $program,
                'exit code ' . $result->status->getCode() . ': ' . $result->stderr,
            );
        }

        return $result->stdout;
    }
}
