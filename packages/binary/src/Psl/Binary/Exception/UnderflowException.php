<?php

declare(strict_types=1);

namespace Psl\Binary\Exception;

use Psl\Exception;

/**
 * @api
 */
final class UnderflowException extends Exception\UnderflowException implements ExceptionInterface {}
