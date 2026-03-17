<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * @immutable
 */
final readonly class ControlSequenceIntroducer implements CommandInterface
{
    public function __construct(
        public string $parameters,
        public ControlSequenceIntroducerKind $kind,
    ) {}

    /**
     * Returns the ANSI escape sequence string.
     *
     * @pure
     */
    public function toString(): string
    {
        return "\e[" . $this->parameters . $this->kind->value;
    }

    /**
     * @pure
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
