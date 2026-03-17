<?php

declare(strict_types=1);

namespace Psl\Shell\Exception;

use function sprintf;
use function str_replace;

use const PHP_EOL;

final class FailedExecutionException extends RuntimeException
{
    private string $command;

    private string $stdoutContent;
    private string $stderrContent;

    public function __construct(string $command, string $stdoutContent, string $stderrContent, int $code)
    {
        $message = sprintf(<<<MESSAGE
        Shell command "%s" returned an exit code of "%d".

        STDOUT:
            %s

        STDERR:
            %s
        MESSAGE, $command, $code, str_replace(PHP_EOL, PHP_EOL . '    ', $stdoutContent), str_replace(PHP_EOL, PHP_EOL . '    ', $stderrContent));

        parent::__construct($message, $code);

        $this->command = $command;
        $this->stdoutContent = $stdoutContent;
        $this->stderrContent = $stderrContent;
    }

    /**
     * @psalm-mutation-free
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @psalm-mutation-free
     */
    public function getOutput(): string
    {
        return $this->stdoutContent;
    }

    /**
     * @psalm-mutation-free
     */
    public function getErrorOutput(): string
    {
        return $this->stderrContent;
    }
}
