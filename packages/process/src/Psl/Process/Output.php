<?php

declare(strict_types=1);

namespace Psl\Process;

/**
 * @api
 */
final readonly class Output
{
    public function __construct(
        public ExitStatus $status,
        public string $stdout,
        public string $stderr,
    ) {}
}
