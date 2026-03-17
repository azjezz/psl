<?php

declare(strict_types=1);

namespace Psl\Comparison\Exception;

use InvalidArgumentException as InvalidArgumentRootException;
use Psl\Exception\ExceptionInterface;

use function get_debug_type;
use function sprintf;

/**
 * Exception thrown when two values are incomparable.
 */
class IncomparableException extends InvalidArgumentRootException implements ExceptionInterface
{
    public static function fromValues(mixed $a, mixed $b, string $additionalInfo = ''): self
    {
        return new self(sprintf(
            'Unable to compare "%s" with "%s"%s',
            get_debug_type($a),
            get_debug_type($b),
            $additionalInfo ? ': ' . $additionalInfo : '.',
        ));
    }
}
