<?php

declare(strict_types=1);

namespace Psl\Type\Exception;

final class TypeTrace
{
    /**
     * @var string[]
     */
    private array $frames = [];

    public function withFrame(string $frame): self
    {
        $self = clone $this;
        $self->frames[] = $frame;

        return $self;
    }

    /**
     * @return string[]
     */
    public function getFrames(): array
    {
        return $this->frames;
    }
}
