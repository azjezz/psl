<?php

declare(strict_types=1);

namespace Psl\DNS\System\HostsFile\Internal;

use Psl\DNS\Exception;
use Psl\DNS\System\HostsFile\HostsFile;
use Psl\Process;

use function getenv;

use const DIRECTORY_SEPARATOR;

/**
 * Loads the system hosts file from the OS-specific path.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Loader
{
    /**
     * Load and parse the system hosts file.
     *
     * @throws Exception\SystemException If the hosts file cannot be read.
     */
    public static function load(): HostsFile
    {
        $path = self::getPath();
        $output = self::execute($path);

        return Parser::parse($output);
    }

    /**
     * @return non-empty-string
     */
    private static function getPath(): string
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $systemRoot = getenv('SystemRoot');
            if ($systemRoot === false || $systemRoot === '') {
                $systemRoot = 'C:\\Windows';
            }

            return $systemRoot . '\\system32\\drivers\\etc\\hosts';
        }

        return '/etc/hosts';
    }

    /**
     * @throws Exception\SystemException If the file cannot be read.
     */
    private static function execute(string $path): string
    {
        $program = DIRECTORY_SEPARATOR === '\\' ? 'cmd' : 'cat';
        $arguments = DIRECTORY_SEPARATOR === '\\' ? ['/c', 'type', $path] : [$path];

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
