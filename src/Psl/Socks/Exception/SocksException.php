<?php

declare(strict_types=1);

namespace Psl\Socks\Exception;

use Psl\Network\Exception;

/**
 * Exception thrown when a SOCKS5 protocol operation fails.
 */
class SocksException extends Exception\RuntimeException implements ExceptionInterface {}
