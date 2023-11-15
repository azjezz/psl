
> [!IMPORTANT]
>
> ## 🇵🇸 Support Palestine 🇵🇸
> 
> In light of recent events in Gaza, I encourage everyone to educate themselves on the ongoing issues in Palestine and consider supporting the people there. Here are some resources and donation links:
>
> - [Decolonize Palestine](https://decolonizepalestine.com/) - An informative resource to better understand the situation in Palestine. Please take the time to read it.
> - [One Ummah - Gaza Emergency Appeal](https://donate.oneummah.org.uk/gazaemergencyappeal48427259) - A platform to provide direct donations to help the people in Gaza.
> - [Islamic Relief UK - Palestine Appeal](https://www.islamic-relief.org.uk/giving/appeals/palestine/) - Another trusted platform to provide support for those affected in Palestine.
>
> Thank you for taking a moment to bring awareness and make a difference. 🇵🇸❤️


# Psl - PHP Standard Library

![Unit tests status](https://github.com/azjezz/psl/workflows/unit%20tests/badge.svg)
![Static analysis status](https://github.com/azjezz/psl/workflows/static%20analysis/badge.svg)
![Security analysis status](https://github.com/azjezz/psl/workflows/security%20analysis/badge.svg)
![Coding standards status](https://github.com/azjezz/psl/workflows/coding%20standards/badge.svg)
![Coding standards status](https://github.com/azjezz/psl/workflows/documentation%20check/badge.svg)
[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/4228/badge)](https://bestpractices.coreinfrastructure.org/projects/4228)
[![Coverage Status](https://coveralls.io/repos/github/azjezz/psl/badge.svg)](https://coveralls.io/github/azjezz/psl)
[![MSI](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fazjezz%2Fpsl%2F2.0.x)](https://dashboard.stryker-mutator.io/reports/github.com/azjezz/psl/2.0.x)
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

use Psl\Async;
use Psl\TCP;
use Psl\IO;
use Psl\Shell;
use Psl\Str;

Async\main(static function(): int {
    IO\write_line('Hello, World!');

    [$version, $connection] = Async\concurrently([
        static fn() => Shell\execute('php', ['-v']),
        static fn() => TCP\connect('localhost', 1337),
    ]);

    $messages = Str\split($version, "\n");
    foreach($messages as $message) {
        $connection->writeAll($message);
    }

    $connection->close();

    return 0;
});
```

## Installation

Supported installation method is via [composer](https://getcomposer.org):

```shell
composer require azjezz/psl
```

### Psalm Integration

Please refer to the [`php-standard-library/psalm-plugin`](https://github.com/php-standard-library/psalm-plugin) repository.

### PHPStan Integration

Please refer to the [`php-standard-library/phpstan-extension`](https://github.com/php-standard-library/phpstan-extension) repository.

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
