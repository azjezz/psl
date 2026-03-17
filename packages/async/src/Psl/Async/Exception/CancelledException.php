<?php

declare(strict_types=1);

namespace Psl\Async\Exception;

use Psl\Async\CancellationTokenInterface;
use Throwable;

/**
 * @mago-expect lint:sensitive-parameter
 */
final class CancelledException extends RuntimeException
{
    public function __construct(
        private readonly CancellationTokenInterface $token,
        null|Throwable $previous = null,
    ) {
        parent::__construct('Operation was cancelled.', 0, $previous);
    }

    /**
     * Returns the cancellation token that triggered this exception.
     */
    public function getToken(): CancellationTokenInterface
    {
        return $this->token;
    }
}
