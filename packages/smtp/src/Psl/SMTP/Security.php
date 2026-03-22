<?php

declare(strict_types=1);

namespace Psl\SMTP;

/**
 * Transport security mode for SMTP connections.
 *
 * @api
 */
enum Security: string
{
    /**
     * No encryption. Traffic is sent in plaintext.
     */
    case None = 'none';

    /**
     * Upgrade a plaintext connection to TLS via the STARTTLS command per RFC 3207.
     */
    case StartTLS = 'starttls';

    /**
     * Implicit TLS. The connection is TLS-encrypted from the start (port 465).
     */
    case TLS = 'tls';
}
