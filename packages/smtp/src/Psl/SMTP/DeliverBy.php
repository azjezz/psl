<?php

declare(strict_types=1);

namespace Psl\SMTP;

use Psl\DateTime\Duration;

/**
 * Delivery deadline specification per RFC 2852.
 *
 * Instructs the server to either return or notify if the message
 * cannot be delivered within the specified deadline.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc2852
 *
 * @api
 */
final readonly class DeliverBy
{
    public function __construct(
        public Duration $deadline,
        public DeliverByMode $mode = DeliverByMode::Return,
    ) {}
}
