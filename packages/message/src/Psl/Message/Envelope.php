<?php

declare(strict_types=1);

namespace Psl\Message;

use Psl\Message\Address\Mailbox;

/**
 * Represents an SMTP transport envelope per RFC 5321.
 *
 * Separates the transport-level sender (MAIL FROM) and recipients (RCPT TO)
 * from the message headers. These may differ from the "From", "To", and "Cc"
 * header fields in the {@see MessageInterface}.
 *
 * Use {@see fromMessage()} to derive an envelope automatically from message headers.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5321
 *
 * @api
 */
final readonly class Envelope
{
    /**
     * The reverse-path (MAIL FROM).
     *
     * Null represents the null reverse-path `<>` used for bounce/DSN messages
     * per RFC 5321 §4.5.5.
     */
    public null|Mailbox $sender;

    /**
     * The forward-paths (RCPT TO).
     *
     * @var non-empty-list<Mailbox>
     */
    public array $recipients;

    /**
     * @param non-empty-list<Mailbox> $recipients
     *
     * @throws Exception\InvalidArgumentException If recipients is empty.
     */
    public function __construct(null|Mailbox $sender, array $recipients)
    {
        if ($recipients === []) {
            throw Exception\InvalidArgumentException::forEmptyRecipients();
        }

        $this->sender = $sender;
        $this->recipients = $recipients;
    }

    /**
     * Derive an envelope from message headers.
     *
     * MAIL FROM is derived from the Sender header, falling back to the first
     * From mailbox, or null if neither is present.
     *
     * RCPT TO is derived from all mailboxes in the To, Cc, and Bcc headers
     * (groups are flattened).
     *
     * @throws Exception\RuntimeException If no recipients can be derived.
     */
    public static function fromMessage(MessageInterface $message): self
    {
        $sender = $message->sender;
        if ($sender === null && $message->from !== null) {
            $fromMailboxes = $message->from->mailboxes();
            $sender = $fromMailboxes[0] ?? null;
        }

        $recipients = [];
        foreach ([$message->to, $message->cc, $message->bcc] as $list) {
            if ($list === null) {
                continue;
            }

            foreach ($list->mailboxes() as $mailbox) {
                $recipients[] = $mailbox;
            }
        }

        if ($recipients === []) {
            throw Exception\RuntimeException::forNoRecipients();
        }

        return new self($sender, $recipients);
    }
}
