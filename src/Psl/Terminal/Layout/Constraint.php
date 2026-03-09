<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * Describes how a layout segment should be sized.
 *
 * @immutable
 */
final readonly class Constraint
{
    /**
     * @param ConstraintKind $kind The constraint kind.
     * @param int $size The fixed or min/max size.
     * @param Constraint|null $inner The inner constraint for min/max wrappers.
     */
    private function __construct(
        public ConstraintKind $kind,
        public int $size,
        public null|Constraint $inner,
    ) {}

    /**
     * Create a fill constraint, takes all remaining space.
     */
    public static function fill(): self
    {
        return new self(ConstraintKind::Fill, 0, null);
    }

    /**
     * Create a fixed-size constraint.
     */
    public static function fixed(int $size): self
    {
        return new self(ConstraintKind::Fixed, $size, null);
    }

    /**
     * Create a minimum-size constraint wrapping another constraint.
     */
    public static function min(int $min, Constraint $constraint): self
    {
        return new self(ConstraintKind::Min, $min, $constraint);
    }

    /**
     * Create a maximum-size constraint wrapping another constraint.
     */
    public static function max(int $max, Constraint $constraint): self
    {
        return new self(ConstraintKind::Max, $max, $constraint);
    }

    public function isFill(): bool
    {
        return $this->kind === ConstraintKind::Fill;
    }

    public function isFixed(): bool
    {
        return $this->kind === ConstraintKind::Fixed;
    }

    public function isMin(): bool
    {
        return $this->kind === ConstraintKind::Min;
    }

    public function isMax(): bool
    {
        return $this->kind === ConstraintKind::Max;
    }
}
