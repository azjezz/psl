<?php

declare(strict_types=1);

namespace Psl\Math\Exception;

use Psl\Exception;

/**
 * @api
 */
final class OverflowException extends Exception\OverflowException implements ExceptionInterface {}
