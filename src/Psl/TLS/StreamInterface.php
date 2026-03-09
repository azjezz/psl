<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\TCP;

/**
 * A TLS-encrypted network stream with access to TLS connection state.
 */
interface StreamInterface extends TCP\StreamInterface
{
    /**
     * Returns the TLS connection state for this connection.
     */
    public function getState(): ConnectionState;
}
