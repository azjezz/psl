<?php

declare(strict_types=1);

namespace Psl\Exception;

use OverflowException as OverflowRootException;

/**
 * @api
 */
class OverflowException extends OverflowRootException implements ExceptionInterface {}
