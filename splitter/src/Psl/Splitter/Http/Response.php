<?php

declare(strict_types=1);

namespace Psl\Splitter\Http;

/**
 * @param array<non-empty-string, non-empty-string> $headers
 */
final readonly class Response
{
    /**
     * @param int<100, 599> $status
     * @param array<non-empty-string, non-empty-string> $headers Lowercased header names.
     */
    public function __construct(
        public int $status,
        public array $headers,
        public string $body,
    ) {}

    public function isOk(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }
}
