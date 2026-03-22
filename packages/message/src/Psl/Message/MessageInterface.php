<?php

declare(strict_types=1);

namespace Psl\Message;

use Psl\DateTime\DateTimeInterface;
use Psl\Message\Address\AddressList;
use Psl\Message\Address\Mailbox;
use Psl\MIME\Headers;
use Psl\MIME\Part\PartInterface;

/**
 * Represents an Internet Message as defined by RFC 5322.
 *
 * Provides read access to the standard header fields (originator, destination,
 * identification, informational) and the message body as a MIME part.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5322
 *
 * @see Message for the default mutable implementation.
 *
 * @api
 *
 * @mago-expect lint:too-many-properties
 */
interface MessageInterface
{
    /**
     * The raw MIME headers of the message.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-2.2
     */
    public Headers $headers { get; }

    /**
     * The originator addresses from the "From" header field (RFC 5322 section 3.6.2).
     *
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.2
     */
    public null|AddressList $from { get; }

    /**
     * The actual sender mailbox from the "Sender" header field (RFC 5322 section 3.6.2).
     *
     * Used when the message has multiple authors in "From" to indicate the
     * single responsible agent. Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.2
     */
    public null|Mailbox $sender { get; }

    /**
     * The primary recipient addresses from the "To" header field (RFC 5322 section 3.6.3).
     *
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.3
     */
    public null|AddressList $to { get; }

    /**
     * The carbon-copy recipient addresses from the "Cc" header field (RFC 5322 section 3.6.3).
     *
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.3
     */
    public null|AddressList $cc { get; }

    /**
     * The blind carbon-copy recipient addresses from the "Bcc" header field (RFC 5322 section 3.6.3).
     *
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.3
     */
    public null|AddressList $bcc { get; }

    /**
     * The reply destination addresses from the "Reply-To" header field (RFC 5322 section 3.6.2).
     *
     * When present, replies should be directed to these addresses rather than to "From".
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.2
     */
    public null|AddressList $replyTo { get; }

    /**
     * The origination date from the "Date" header field (RFC 5322 section 3.6.1).
     *
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.1
     */
    public null|DateTimeInterface $date { get; }

    /**
     * The unique message identifier from the "Message-ID" header field (RFC 5322 section 3.6.4).
     *
     * Null when the header is absent or could not be parsed.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.4
     */
    public null|MessageId $messageId { get; }

    /**
     * The decoded subject line from the "Subject" header field (RFC 5322 section 3.6.5).
     *
     * RFC 2047 encoded-words are decoded automatically. Null when the header is absent.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.5
     * @link https://datatracker.ietf.org/doc/html/rfc2047
     */
    public null|string $subject { get; }

    /**
     * The message identifiers from the "References" header field (RFC 5322 section 3.6.4).
     *
     * Used for threading; contains the chain of message IDs in the conversation.
     *
     * @var list<MessageId>
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.4
     */
    public array $references { get; }

    /**
     * The message identifiers from the "In-Reply-To" header field (RFC 5322 section 3.6.4).
     *
     * Identifies the message(s) this message is a reply to.
     *
     * @var list<MessageId>
     *
     * @link https://datatracker.ietf.org/doc/html/rfc5322#section-3.6.4
     */
    public array $inReplyTo { get; }

    /**
     * The message body as a MIME part (RFC 2045).
     *
     * For multipart messages, the part can be further decomposed using
     * {@see \Psl\MIME\MultiPart\Parser}.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc2045
     */
    public PartInterface $content { get; }
}
