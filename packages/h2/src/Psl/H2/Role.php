<?php

declare(strict_types=1);

namespace Psl\H2;

use Psl\H2\Frame\FrameType;

/**
 * HTTP/2 connection role.
 *
 * Identifies whether an endpoint is acting as a client or server in the HTTP/2 connection.
 * The role determines which stream identifiers an endpoint may initiate (clients use
 * odd-numbered streams, servers use even-numbered streams) and affects the connection
 * preface exchange behavior as described in RFC 9113 Section 3.4.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4
 */
enum Role
{
    /**
     * The client role: the endpoint that initiates the HTTP/2 connection.
     *
     * The client sends the connection preface ({@see CONNECTION_PREFACE}) which begins
     * with a 24-octet sequence followed by a SETTINGS frame ({@see FrameType::Settings}).
     * The client initiates streams using odd-numbered stream identifiers (1, 3, 5, ...).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1.1
     */
    case Client;

    /**
     * The server role: the endpoint that accepts the HTTP/2 connection.
     *
     * The server sends its own SETTINGS frame ({@see FrameType::Settings}) as a connection
     * preface after receiving the client preface. The server initiates streams (for server
     * push) using even-numbered stream identifiers (2, 4, 6, ...).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4
     * @link https://datatracker.ietf.org/doc/html/rfc9113#section-5.1.1
     */
    case Server;
}
