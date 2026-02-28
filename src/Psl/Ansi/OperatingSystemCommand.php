<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * @immutable
 */
final readonly class OperatingSystemCommand implements CommandInterface
{
    public function __construct(
        public OperatingSystemCommandKind $kind,
        public string $data,
    ) {}

    /**
     * Returns the OSC escape sequence string.
     *
     * @pure
     */
    public function toString(): string
    {
        return "\e]" . $this->kind->value . ';' . $this->data . "\e\\";
    }

    /**
     * @pure
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
