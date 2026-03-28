<?php

declare(strict_types=1);

namespace Psl\Json\Exception;

use Psl\Exception\InvalidArgumentException;

/**
 * @api
 */
final class EncodeException extends InvalidArgumentException implements ExceptionInterface {}
