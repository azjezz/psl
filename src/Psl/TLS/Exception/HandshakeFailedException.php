<?php

declare(strict_types=1);

namespace Psl\TLS\Exception;

use Psl\Network\Exception;

/**
 * Exception thrown when a TLS handshake fails.
 */
final class HandshakeFailedException extends Exception\RuntimeException implements ExceptionInterface {}
