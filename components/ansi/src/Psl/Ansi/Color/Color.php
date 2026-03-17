<?php

declare(strict_types=1);

namespace Psl\Ansi\Color;

use Psl\Ansi\Exception;

/**
 * @immutable
 */
final readonly class Color
{
    private function __construct(
        private ColorKind $kind,
        private int $value,
        private int $red,
        private int $green,
        private int $blue,
    ) {}

    /**
     * Creates a basic ANSI color from a foreground code.
     */
    public static function basic(int $value): self
    {
        return new self(ColorKind::Basic, $value, 0, 0, 0);
    }

    /**
     * Creates an ANSI-256 color from a palette index.
     *
     * @throws Exception\InvalidArgumentException If $code is not in the range 0-255.
     */
    public static function ansi256(int $code): self
    {
        if ($code < 0 || $code > 255) {
            throw new Exception\InvalidArgumentException(
                'Expected an ANSI-256 color code between 0 and 255, got ' . $code . '.',
            );
        }

        return new self(ColorKind::Ansi256, $code, 0, 0, 0);
    }

    /**
     * Creates an RGB color.
     *
     * @throws Exception\InvalidArgumentException If any component is not in the range 0-255.
     */
    public static function rgb(int $red, int $green, int $blue): self
    {
        if ($red < 0 || $red > 255) {
            throw new Exception\InvalidArgumentException('Expected red component between 0 and 255, got ' . $red . '.');
        }

        if ($green < 0 || $green > 255) {
            throw new Exception\InvalidArgumentException(
                'Expected green component between 0 and 255, got ' . $green . '.',
            );
        }

        if ($blue < 0 || $blue > 255) {
            throw new Exception\InvalidArgumentException(
                'Expected blue component between 0 and 255, got ' . $blue . '.',
            );
        }

        return new self(ColorKind::Rgb, 0, $red, $green, $blue);
    }

    public function getKind(): ColorKind
    {
        return $this->kind;
    }

    /**
     * Returns the ANSI color code for Basic colors, or the palette index for ANSI-256 colors.
     *
     * @throws Exception\LogicException If the color is RGB.
     */
    public function getValue(): int
    {
        if ($this->kind === ColorKind::Rgb) {
            throw new Exception\LogicException('Cannot retrieve value from an RGB color.');
        }

        return $this->value;
    }

    /**
     * @throws Exception\LogicException If the color is not RGB.
     */
    public function getRed(): int
    {
        if ($this->kind !== ColorKind::Rgb) {
            throw new Exception\LogicException('Cannot retrieve red component from a non-RGB color.');
        }

        return $this->red;
    }

    /**
     * @throws Exception\LogicException If the color is not RGB.
     */
    public function getGreen(): int
    {
        if ($this->kind !== ColorKind::Rgb) {
            throw new Exception\LogicException('Cannot retrieve green component from a non-RGB color.');
        }

        return $this->green;
    }

    /**
     * Returns true if this color has the same kind and values as another color.
     */
    public function equals(self $other): bool
    {
        return (
            $this->kind === $other->kind
            && $this->value === $other->value
            && $this->red === $other->red
            && $this->green === $other->green
            && $this->blue === $other->blue
        );
    }

    /**
     * @throws Exception\LogicException If the color is not RGB.
     */
    public function getBlue(): int
    {
        if ($this->kind !== ColorKind::Rgb) {
            throw new Exception\LogicException('Cannot retrieve blue component from a non-RGB color.');
        }

        return $this->blue;
    }
}
