<?php

declare(strict_types=1);

namespace Psl\H2\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base runtime exception for the H2 component.
 *
 * All concrete H2 exceptions extend this class, providing a common
 * catch point for any error that occurs during HTTP/2 processing.
 *
 * @inheritors ProtocolException|StreamException|FrameDecodingException|FlowControlException|ConnectionException
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
