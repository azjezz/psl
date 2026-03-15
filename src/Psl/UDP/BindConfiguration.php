<?php

declare(strict_types=1);

namespace Psl\UDP;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Configuration for UDP socket bind operations.
 */
final readonly class BindConfiguration implements DefaultInterface
{
    /**
     * @param bool $reuseAddress Allow reuse of local addresses.
     * @param bool $reusePort Allow multiple sockets to bind to the same port.
     * @param bool $broadcast Enable sending broadcast datagrams.
     */
    public function __construct(
        public bool $reuseAddress = false,
        public bool $reusePort = false,
        public bool $broadcast = false,
    ) {}

    #[Override]
    public static function default(): static
    {
        return new self();
    }
}
