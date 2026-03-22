<?php

declare(strict_types=1);

namespace Psl\SMTP;

/**
 * ESMTP capabilities advertised in EHLO responses per RFC 5321.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5321#section-2.2
 *
 * @api
 */
enum Capability: string
{
    /**
     * 8-bit MIME transport per RFC 6152.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6152
     */
    case EightBitMIME = '8BITMIME';

    /**
     * Message size declaration per RFC 1870.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc1870
     */
    case Size = 'SIZE';

    /**
     * TLS upgrade via STARTTLS per RFC 3207.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3207
     */
    case StartTLS = 'STARTTLS';

    /**
     * Command pipelining per RFC 2920.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc2920
     */
    case Pipelining = 'PIPELINING';

    /**
     * Delivery status notifications per RFC 3461.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3461
     */
    case DSN = 'DSN';

    /**
     * Internationalized email per RFC 6531.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6531
     */
    case SMTPUTF8 = 'SMTPUTF8';

    /**
     * Chunked message transfer per RFC 3030.
     *
     * Allows sending message data via BDAT commands instead of DATA,
     * avoiding dot-stuffing overhead.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3030
     */
    case Chunking = 'CHUNKING';

    /**
     * Binary MIME transfer per RFC 3030.
     *
     * Allows sending binary content via BDAT without transfer encoding,
     * paired with the CHUNKING capability.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3030
     */
    case BinaryMIME = 'BINARYMIME';

    /**
     * Require TLS for the entire delivery chain per RFC 8689.
     *
     * When used, the server must ensure TLS is maintained for all
     * subsequent hops or bounce the message.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc8689
     */
    case RequireTLS = 'REQUIRETLS';

    /**
     * Delivery deadline per RFC 2852.
     *
     * Allows the sender to specify a time limit for message delivery.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc2852
     */
    case DeliverBy = 'DELIVERBY';

    /**
     * Deferred delivery per RFC 4865.
     *
     * Allows the sender to request that the message be held for
     * future delivery at a specified time.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4865
     */
    case FutureRelease = 'FUTURERELEASE';

    /**
     * Message priority per RFC 6710.
     *
     * Allows the sender to assign a priority level (-9 to 9) to
     * influence message processing order.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6710
     */
    case MTPriority = 'MT-PRIORITY';
}
