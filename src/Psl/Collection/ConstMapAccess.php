<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for enabling access to the Maps values.
 *
 * This interface provides no new methods as all current access for Maps are defined in its parent interfaces.
 * But you could theoretically use this interface for parameter and return type annotations.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @template-extends ConstSetAccess<Tk, Tv>
 * @template-extends ConstIndexAccess<Tk, Tv>
 */
interface ConstMapAccess extends ConstSetAccess, ConstIndexAccess
{
}
