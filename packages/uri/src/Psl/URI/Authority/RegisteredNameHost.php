<?php

declare(strict_types=1);

namespace Psl\URI\Authority;

/**
 * Represents a registered name host in a URI per RFC 3986.
 *
 * A registered name is a DNS hostname or any other non-IP host identifier.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.2.2
 */
final readonly class RegisteredNameHost implements HostInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
    ) {}

    /**
     * Return the registered name as-is.
     *
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
