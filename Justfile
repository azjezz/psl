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

docs-test:
    php docs/test-examples.php

preload:
    php src/preload.php

php:
    which php

verify: fmt-diff lint analyze test mutation docs-test
