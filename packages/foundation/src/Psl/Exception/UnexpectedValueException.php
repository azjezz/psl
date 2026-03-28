<?php

declare(strict_types=1);

namespace Psl\Exception;

use UnexpectedValueException as UnexpectedValueRootException;

/**
 * @api
 */
class UnexpectedValueException extends UnexpectedValueRootException implements ExceptionInterface {}
