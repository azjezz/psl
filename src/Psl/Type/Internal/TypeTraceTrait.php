<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\TypeTrace;

trait TypeTraceTrait
{
    private ?TypeTrace $trace = null;

    final protected function getTrace(): TypeTrace
    {
        return $this->trace ?? new TypeTrace();
    }

    final protected function withTrace(TypeTrace $trace): self
    {
        $new = clone $this;
        $new->trace = $trace;
        return $new;
    }
}
