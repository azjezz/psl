<?php

declare(strict_types=1);

namespace Psl\Shell\Exception;

use Psl\Str;

final class FailedExecutionException extends RuntimeException implements ExceptionInterface
{
    private string $command;

    private string $stdoutContent;
    private string $stderrContent;

    public function __construct(string $command, string $stdout_content, string $stderr_content, int $code)
    {
        $message = Str\format('Shell command "%s" returned an exit code of "%d".', $command, $code);

        parent::__construct($message, $code);

        $this->command = $command;
        $this->stdoutContent = $stdout_content;
        $this->stderrContent = $stderr_content;
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
