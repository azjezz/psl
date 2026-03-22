<?php

declare(strict_types=1);

namespace Psl\SMTP;

/**
 * Message transfer priority levels per RFC 6710 / STANAG 4406.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6710
 *
 * @api
 */
enum Priority: int
{
    /**
     * Deferred processing priority.
     */
    case Deferred = -6;

    /**
     * Non-urgent priority (MIXER/X.400 "non-urgent").
     */
    case NonUrgent = -4;

    /**
     * Priority processing level.
     */
    case Priority = -2;

    /**
     * Normal processing priority (MIXER/X.400 "normal").
     */
    case Normal = 0;

    /**
     * Flash priority for time-sensitive messages.
     */
    case Flash = 2;

    /**
     * Urgent priority (MIXER/X.400 "urgent").
     */
    case Urgent = 4;
}
