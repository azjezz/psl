<?php

declare(strict_types=1);

namespace Psl\Exception;

use UnderflowException as UnderflowRootException;

/**
 * @api
 */
class UnderflowException extends UnderflowRootException implements ExceptionInterface {}
