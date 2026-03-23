<?php

declare(strict_types=1);

namespace Psl\DNS\Exception;

use Psl\Exception;
use Throwable;

/**
 * Base runtime exception for DNS operations.
 *
 * @api
 *
 * @inheritors NetworkException|SystemException|ProtocolException
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
    protected function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for a failed DNS transaction ID generation.
     */
    public static function forTransactionIDGenerationFailure(Throwable $previous): self
    {
        return new self('Failed to generate DNS transaction ID.', $previous);
    }

    /**
     * Create an exception for a failed DNS cookie generation.
     */
    public static function forCookieGenerationFailure(Throwable $previous): self
    {
        return new self('Failed to generate DNS cookie.', $previous);
    }

    /**
     * Create an exception when all resolvers in a composite resolver have failed.
     *
     * @param non-empty-string $resolverType The type of composite resolver that failed (e.g. "fallback" or "racing").
     */
    public static function forAllResolversFailed(string $resolverType, null|Throwable $previous = null): self
    {
        return new self('All ' . $resolverType . ' resolvers failed.', $previous);
    }

    /**
     * Create an exception for a server-side DNS error response.
     */
    public static function forServerError(string $responseCode): self
    {
        return new self('DNS server returned ' . $responseCode . '.');
    }
}
