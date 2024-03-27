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

    /**
     * @var list<string> $path
     */
    private array $path = [];

    public function withFrame(string $frame): self
    {
        $self           = clone $this;
        $self->frames[] = $frame;

        return $self;
    }

    public function withFrameAtPath(string $frame, string $path): self
    {
        $self           = clone $this;
        $self->frames[] = $frame;
        $self->path[] = $path;

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

    /**
     * @return list<string>
     *
     * @psalm-mutation-free
     */
    public function getPath(): array
    {
        return $this->path;
    }
}
