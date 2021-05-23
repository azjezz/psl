# Psl - PHP Standard Library

![Unit tests status](https://github.com/azjezz/psl/workflows/unit%20tests/badge.svg)
![Static analysis status](https://github.com/azjezz/psl/workflows/static%20analysis/badge.svg)
![Security analysis status](https://github.com/azjezz/psl/workflows/security%20analysis/badge.svg)
![Coding standards status](https://github.com/azjezz/psl/workflows/coding%20standards/badge.svg)
![Coding standards status](https://github.com/azjezz/psl/workflows/documentation%20check/badge.svg)
[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/4228/badge)](https://bestpractices.coreinfrastructure.org/projects/4228)
[![Coverage Status](https://coveralls.io/repos/github/azjezz/psl/badge.svg)](https://coveralls.io/github/azjezz/psl)
[![Type Coverage](https://shepherd.dev/github/azjezz/psl/coverage.svg)](https://shepherd.dev/github/azjezz/psl)
[![Total Downloads](https://poser.pugx.org/azjezz/psl/d/total.svg)](https://packagist.org/packages/azjezz/psl)
[![Latest Stable Version](https://poser.pugx.org/azjezz/psl/v/stable.svg)](https://packagist.org/packages/azjezz/psl)
[![License](https://poser.pugx.org/azjezz/psl/license.svg)](https://packagist.org/packages/azjezz/psl)

Psl is a standard library for PHP, inspired by [hhvm/hsl](https://github.com/hhvm/hsl).

The goal of Psl is to provide a consistent, centralized, well-typed set of APIs for PHP programmers.

## Example

```php
<?php

declare(strict_types=1);

use Psl\{Str, Vec};

/**
 * @psalm-param iterable<?int> $codes
 */
function foo(iterable $codes): string
{
    $codes = Vec\filter_nulls($codes);

    $chars = Vec\map($codes, fn(int $code): string => Str\chr($code));

    return Str\join($chars, ', ');
}

foo([95, 96, null, 98]);
// 'a, b, d'
```

## Installation

Supported installation method is via [composer](https://getcomposer.org):

```shell
composer require azjezz/psl
```

### Psalm Integration

Please refer to the [`php-standard-library/psalm-plugin`](https://github.com/php-standard-library/psalm-plugin) repository.

## Documentation

You can read through the API documentation in [`docs/`](./docs) directory.

## Interested in contributing?

Have a look at [`CONTRIBUTING.md`](./CONTRIBUTING.md).

## Sponsors

Thanks to our sponsors and supporters:

| JetBrains |
|---|
| <a href="https://www.jetbrains.com/?from=PSL ( PHP Standard Library )" title="JetBrains" target="_blank"><img src="https://res.cloudinary.com/azjezz/image/upload/v1599239910/jetbrains_qnyb0o.png" height="120" /></a> |

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
