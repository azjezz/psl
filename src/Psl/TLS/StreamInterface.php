<?php

declare(strict_types=1);

namespace Psl\TLS;

use Psl\Network;

/**
 * A TLS-encrypted network stream with access to TLS connection state.
 */
interface StreamInterface extends Network\StreamInterface
{
    /**
     * Returns the TLS connection state for this connection.
     */
    public function getState(): ConnectionState;
}
