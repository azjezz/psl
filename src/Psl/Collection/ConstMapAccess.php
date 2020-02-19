<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for enabling access to the Maps values.
 *
 * This interface provides no new methods as all current access for Maps are defined in its parent interfaces.
 * But you could theoretically use this interface for parameter and return type annotations.
 *
 * @template Tk
 * @template Tv
 *
 * @extends ConstSetAccess<Tk>
 * @extends ConstIndexAccess<Tk, Tv>
 */
interface ConstMapAccess extends ConstSetAccess, ConstIndexAccess
{
}
