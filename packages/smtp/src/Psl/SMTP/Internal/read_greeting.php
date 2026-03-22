<?php

declare(strict_types=1);

namespace Psl\SMTP\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\IO;
use Psl\SMTP\Exception\ConnectionException;
use Psl\SMTP\Exception\ProtocolException;
use Psl\SMTP\Reply;

/**
 * Read and validate the SMTP server greeting.
 *
 * The greeting must be a 220 response per RFC 5321 §3.1.
 *
 * @internal
 *
 * @throws ConnectionException If the greeting is not a 220 response.
 * @throws ProtocolException If the response is malformed.
 * @throws IO\Exception\RuntimeException If reading fails.
 * @throws CancelledException If reading is cancelled.
 */
function read_greeting(IO\Reader $reader, CancellationTokenInterface $cancellation = new NullCancellationToken()): Reply
{
    $response = namespace\parse_reply($reader, $cancellation);
    if ($response->code !== 220) {
        throw ConnectionException::forUnexpectedGreeting($response->code, $response->message);
    }

    return $response;
}
