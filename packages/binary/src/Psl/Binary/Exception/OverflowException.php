<?php

declare(strict_types=1);

namespace Psl\Binary\Exception;

use Psl\Exception;

/**
 * @api
 */
final class OverflowException extends Exception\OverflowException implements ExceptionInterface {}
