<?php

declare(strict_types=1);

namespace Psl\Xml\Issue;

/**
 * @psalm-immutable
 */
final class Level
{
    private const LEVEL_WARNING = 1;
    private const LEVEL_ERROR = 2;
    private const LEVEL_FATAL = 3;

    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @psalm-pure
     */
    public static function error(): self
    {
        return new self(self::LEVEL_ERROR);
    }

    /**
     * @psalm-pure
     */
    public static function fatal(): self
    {
        return new self(self::LEVEL_FATAL);
    }

    /**
     * @psalm-pure
     */
    public static function warning(): self
    {
        return new self(self::LEVEL_WARNING);
    }

    public function value(): int
    {
        return $this->value;
    }

    /**
     * @return 'warning'|'fatal'|'error'
     */
    public function toString(): string
    {
        if ($this->isWarning()) {
            return 'warning';
        }

        if ($this->isError()) {
            return 'error';
        }

        return 'fatal';
    }

    public function matches(Level $level): bool
    {
        return $level->value() === $this->value;
    }

    public function isError(): bool
    {
        return $this->matches(self::error());
    }

    public function isFatal(): bool
    {
        return $this->matches(self::fatal());
    }

    public function isWarning(): bool
    {
        return $this->matches(self::warning());
    }
}
