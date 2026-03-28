<?php

declare(strict_types=1);

namespace Psl\Option\Exception;

use Psl\Exception\UnderflowException;

/**
 * @api
 */
final class NoneException extends UnderflowException implements ExceptionInterface {}
