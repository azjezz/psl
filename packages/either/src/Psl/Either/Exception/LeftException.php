<?php

declare(strict_types=1);

namespace Psl\Either\Exception;

use Psl\Exception\UnderflowException;

/**
 * @api
 */
final class LeftException extends UnderflowException implements ExceptionInterface {}
