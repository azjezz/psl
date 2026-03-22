<?php

declare(strict_types=1);

namespace Psl\SMTP\Client;

use Psl\Message\Address\Mailbox;
use Psl\SMTP\Reply;

/**
 * Report of a message delivery attempt.
 *
 * Contains per-recipient rejection details. When all recipients are accepted,
 * the rejected list is empty.
 *
 * @api
 */
final readonly class DeliveryReport
{
    /**
     * Recipients that were rejected by the server.
     *
     * Each entry is a pair of the rejected mailbox and the server's reply.
     *
     * @var list<array{Mailbox, Reply}>
     */
    public array $rejectedRecipients;

    /**
     * @param list<array{Mailbox, Reply}> $rejectedRecipients
     */
    public function __construct(array $rejectedRecipients = [])
    {
        $this->rejectedRecipients = $rejectedRecipients;
    }

    /**
     * Whether any recipients were rejected.
     */
    public function hasRejections(): bool
    {
        return $this->rejectedRecipients !== [];
    }
}
