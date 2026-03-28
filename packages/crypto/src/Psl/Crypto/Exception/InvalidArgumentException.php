<?php

declare(strict_types=1);

namespace Psl\Crypto\Exception;

use Psl\Exception;

/**
 * @api
 */
final class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface {}
