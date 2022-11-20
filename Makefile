php-config           := opcache.enable_cli: true, opcache.jit_buffer_size: 256M, opcache.enable_cli: true, opcache.enable: true
php-extension-config := $(php-config), extension: extension/target/release/libpsl.so

help:                                                                           ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:                                                              			## install all dependencies for a development environment
	composer install

coding-standard-fix:                                                            ## apply automated coding standard fixes
	PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix --config=config/.php_cs.dist.php
	./vendor/bin/phpcbf --basepath=. --standard=config/.phpcs.xml

coding-standard-check:                                                          ## check coding-standard compliance
	PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix --config=config/.php_cs.dist.php --dry-run
	./vendor/bin/phpcs --basepath=. --standard=config/.phpcs.xml

benchmarks:                                                                     ## run benchmarks
	./vendor/bin/phpbench run --config config/phpbench.json

create-benchmark-reference:                                                     ## run benchmarks, mark current run as "reference"
	./vendor/bin/phpbench run --config config/phpbench.json --tag=benchmark_reference

compare-benchmark-to-reference:                                                 ## run benchmarks, compare result to the "reference" run
	./vendor/bin/phpbench run --config config/phpbench.json --ref=benchmark_reference

static-analysis:                                                                ## run static analysis checks
	./vendor/bin/psalm -c config/psalm.xml --show-info=true --no-cache
	./vendor/bin/psalm -c config/psalm.xml tests/static-analysis --no-cache

type-coverage:                                                                  ## send static analysis type coverage metrics to https://shepherd.dev/
	./vendor/bin/psalm -c config/psalm.xml --shepherd --stats

security-analysis:                                                              ## run static analysis security checks
	./vendor/bin/psalm -c config/psalm.xml --taint-analysis

unit-tests:                                                                     ## run unit test suite
	./vendor/bin/phpunit -c config/phpunit.xml.dist

mutation-tests:                                                                     ## run mutation tests
	./vendor/bin/roave-infection-static-analysis-plugin run --configuration=config/infection.json.dist --psalm-config=config/psalm.xml

code-coverage: unit-tests                                                       ## generate and upload test coverage metrics to https://coveralls.io/
	./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

docs-generate:                                                                  ## regenerate docs
	php docs/documenter.php

docs-check:                                                                     ## checks if docs are up to date
	php docs/documenter.php check

check: coding-standard-check static-analysis security-analysis unit-tests mutation-tests docs-check  ## run quick checks for local development iterations

compile:
	cd extension; cargo build -r;

benchmark-extension: compile
	php vendor/bin/phpbench run --group math --group ds --config config/phpbench.json --tag=php --php-config='$(php-config)'
	php vendor/bin/phpbench run --group math --group ds --config config/phpbench.json --tag=extension --ref=php --php-config='$(php-extension-config)' --report=aggregate
