<?php

declare(strict_types=1);

namespace Psl\SMTP\Client;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Message\Envelope;
use Psl\Message\MessageInterface;
use Psl\SMTP\Exception;

/**
 * Contract for sending messages over SMTP.
 *
 * @api
 */
interface TransportInterface
{
    /**
     * Send a message with its envelope through the SMTP transport.
     *
     * Returns a {@see DeliveryReport} containing per-recipient rejection details.
     * When all recipients are accepted, the report has no rejections.
     *
     * @throws Exception\PossibleAttackException If the envelope, message, DSN, or configuration contain CRLF or null bytes that could be used for SMTP command injection.
     * @throws Exception\RuntimeException If any part of the SMTP exchange fails.
     * @throws CancelledException If the operation is cancelled.
     */
    public function send(
        Envelope $envelope,
        MessageInterface $message,
        SendConfiguration $sendConfiguration = new SendConfiguration(),
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): DeliveryReport;

    /**
     * Close all pooled connections.
     */
    public function close(): void;
}
