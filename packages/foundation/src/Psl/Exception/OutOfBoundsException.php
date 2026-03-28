<?php

declare(strict_types=1);

namespace Psl\Exception;

use OutOfBoundsException as OutOfBoundsRootException;

/**
 * @api
 */
class OutOfBoundsException extends OutOfBoundsRootException implements ExceptionInterface {}
