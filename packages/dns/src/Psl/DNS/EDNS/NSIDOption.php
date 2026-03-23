<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

/**
 * Name Server Identifier option (RFC 5001, option code 3).
 *
 * Allows clients to request and servers to provide a unique identifier
 * for the specific name server instance that handled the query.
 *
 * @api
 */
final class NSIDOption implements OptionInterface
{
    /**
     * {@inheritDoc}
     */
    public int $code {
        get => 3;
    }

    /**
     * @param string $id Opaque server identifier; empty in queries.
     */
    public function __construct(
        public readonly string $id = '',
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toWireFormat(): string
    {
        return $this->id;
    }
}
