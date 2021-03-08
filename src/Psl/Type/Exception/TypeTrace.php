<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

/**
 * @psalm-immutable
 */
final class TypeTrace
{
    /**
     * @var list<string> $frames
     */
    private array $frames = [];

    public function withFrame(string $frame): self
    {
        $self           = clone $this;
        $self->frames[] = $frame;

        return $self;
    }

    /**
     * @return list<string>
     *
     * @psalm-mutation-free
     */
    public function getFrames(): array
    {
        return $this->frames;
    }
}
