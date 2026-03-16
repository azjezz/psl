<?php

declare(strict_types=1);

namespace Psl\Encoding\Exception;

use function sprintf;

final class ParsingException extends RuntimeException implements ExceptionInterface
{
    public static function forInvalidEncodedWord(string $text): self
    {
        return new self(sprintf('Failed to decode encoded-word payload: "%s".', $text));
    }
}
