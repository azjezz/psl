list:
    @just --list

install:
    composer install

fmt:
    ./vendor/bin/mago --config config/mago.toml fmt

fmt-diff:
    ./vendor/bin/mago --config config/mago.toml fmt --diff

fmt-check:
    ./vendor/bin/mago --config config/mago.toml fmt --check

lint:
    ./vendor/bin/mago --config config/mago.toml lint

fix:
    ./vendor/bin/mago --config config/mago.toml analyze --fix
    ./vendor/bin/mago --config config/mago.toml lint --fix
    ./vendor/bin/mago --config config/mago.toml fmt

analyze:
    ./vendor/bin/mago --config config/mago.toml analyze

bench:
    ./vendor/bin/phpbench run --config config/phpbench.json

bench-reference:
    ./vendor/bin/phpbench run --config config/phpbench.json --tag=benchmark_reference

bench-compare:
    ./vendor/bin/phpbench run --config config/phpbench.json --ref=benchmark_reference

test:
    php -dmemory_limit=-1 ./vendor/bin/phpunit -c config/phpunit.xml.dist

mutation:
    php -dmemory_limit=-1 -dopcache.enable=0 ./vendor/bin/infection --configuration=config/infection.json.dist

coverage:
    php -dmemory_limit=-1 ./vendor/bin/phpunit -c config/phpunit.xml.dist --coverage-clover var/clover.xml
    php -dmemory_limit=-1 ./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

docs:
    php docs/generate.php

docs-serve: docs
    php -S localhost:8000 -t docs/dist

php:
    which php

split-install:
    cd splitter && composer install

split-check:
    cd splitter && php bin/splitter check

split branch:
    cd splitter && php bin/splitter split --branch {{branch}}

split-tag tag:
    cd splitter && php bin/splitter tag {{tag}}

split-release tag:
    cd splitter && php bin/splitter release {{tag}}

split-prepare version:
    cd splitter && php bin/splitter prepare {{version}}

split-audit:
    cd splitter && php bin/splitter audit

install-packages:
    #!/usr/bin/env bash
    set -euo pipefail
    for dir in packages/*/; do
        comp=$(basename "$dir")
        cd "$dir"
        composer update
        cd ../..
    done

install-packages-lowest:
    #!/usr/bin/env bash
    set -euo pipefail
    for dir in packages/*/; do
        comp=$(basename "$dir")
        cd "$dir"
        composer update --prefer-lowest --prefer-stable
        cd ../..
    done

test-packages:
    #!/usr/bin/env bash
    set -euo pipefail
    for dir in packages/*/; do
        comp=$(basename "$dir")
        cd "$dir"
        vendor/bin/phpunit --no-coverage
        cd ../..
    done

verify: fmt-diff lint analyze split-check test mutation
