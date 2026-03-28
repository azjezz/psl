<?php

declare(strict_types=1);

namespace Psl\Punycode\Exception;

use Psl\Exception;

/**
 * Marker interface for all Punycode component exceptions.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3492
 *
 * @api
 */
interface ExceptionInterface extends Exception\ExceptionInterface {}
