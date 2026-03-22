<?php

declare(strict_types=1);

namespace Psl\SMTP\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\SMTP\EnhancedStatusCode;
use Psl\SMTP\Exception\ProtocolException;
use Psl\SMTP\Reply;

use function preg_match;
use function rtrim;
use function strlen;
use function substr;

/**
 * Parse a single or multi-line SMTP response from a reader.
 *
 * SMTP responses follow RFC 5321 SS4.2:
 * - Single-line: "250 OK\r\n"
 * - Multi-line: "250-First line\r\n250 Last line\r\n"
 *
 * If the text portion begins with an RFC 3463 enhanced status code
 * (e.g. "2.1.0"), it is extracted into a structured {@see EnhancedStatusCode}
 * and removed from the message text.
 *
 * @internal
 *
 * @throws ProtocolException If the response is malformed.
 * @throws IO\Exception\RuntimeException If reading fails.
 * @throws CancelledException If reading is cancelled.
 */
function parse_reply(IO\Reader $reader, CancellationTokenInterface $cancellation = new NullCancellationToken()): Reply
{
    $message = '';
    $code = null;

    while (true) {
        $rawLine = $reader->readLine($cancellation);
        if ($rawLine === null) {
            throw ProtocolException::forMalformedResponse('');
        }

        $line = rtrim($rawLine, "\r");

        if (strlen($line) < 3) {
            throw ProtocolException::forMalformedResponse($line);
        }

        $lineCodeString = substr($line, 0, 3);
        $lineCode = (int) $lineCodeString;
        if ($lineCode < 100 || $lineCode > 599 || $lineCodeString !== (string) $lineCode) {
            throw ProtocolException::forMalformedResponse($line);
        }

        if ($code === null) {
            $code = $lineCode;
        } elseif ($lineCode !== $code) {
            throw ProtocolException::forMalformedResponse($line);
        }

        $separator = substr($line, 3, 1);
        $text = strlen($line) > 4 ? substr($line, 4) : '';

        if ($message !== '') {
            $message .= "\n";
        }

        $message .= $text;

        if ($separator === ' ' || $separator === '') {
            break;
        }

        if ($separator !== '-') {
            throw ProtocolException::forMalformedResponse($line);
        }
    }

    /** @var int $code */
    $enhancedStatus = null;
    $matches = null;
    if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})(?: (.*))?$/s', $message, $matches)) {
        /**
         * @var array{1: numeric-string, 2: numeric-string, 3: numeric-string, 4?: string} $matches
         * @var int<0, 999> $class
         */
        $class = (int) $matches[1];
        /** @var int<0, 999> $subject */
        $subject = (int) $matches[2];
        /** @var int<0, 999> $detail */
        $detail = (int) $matches[3];

        $enhancedStatus = new EnhancedStatusCode($class, $subject, $detail);
        $message = $matches[4] ?? '';
    }

    return new Reply($code, $enhancedStatus, $message);
}
