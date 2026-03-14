<?php

declare(strict_types=1);

namespace Psl\Encoding\Exception;

use Psl\Str;

final class ParsingException extends RuntimeException implements ExceptionInterface
{
    public static function forInvalidEncodedWord(string $text): self
    {
        return new self(Str\format('Failed to decode encoded-word payload: "%s".', $text));
    }
}
