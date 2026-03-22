<?php

declare(strict_types=1);

namespace Psl\SMTP;

/**
 * Delivery deadline mode per RFC 2852.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2852
 *
 * @api
 */
enum DeliverByMode: string
{
    /**
     * Return the message as undeliverable if the deadline expires.
     */
    case Return = 'R';

    /**
     * Notify the sender if the deadline expires, but continue delivery attempts.
     */
    case Notify = 'N';
}
