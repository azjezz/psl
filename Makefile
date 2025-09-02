help:                                                                           ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install:                                                              			## install all dependencies for a development environment
	composer install

coding-standard-fix:                                                            ## apply automated coding standard fixes
	./vendor/bin/mago --config config/mago.toml lint --fix
	./vendor/bin/mago --config config/mago.toml fmt

coding-standard-check:                                                          ## check coding-standard compliance
	./vendor/bin/mago --config config/mago.toml lint
	./vendor/bin/mago --config config/mago.toml fmt --check

static-analysis:                                                                ## run static analysis checks
	./vendor/bin/mago --config config/mago.toml analyze

benchmarks:                                                                     ## run benchmarks
	./vendor/bin/phpbench run --config config/phpbench.json

create-benchmark-reference:                                                     ## run benchmarks, mark current run as "reference"
	./vendor/bin/phpbench run --config config/phpbench.json --tag=benchmark_reference

compare-benchmark-to-reference:                                                 ## run benchmarks, compare result to the "reference" run
	./vendor/bin/phpbench run --config config/phpbench.json --ref=benchmark_reference

unit-tests:                                                                     ## run unit test suite
	php -dmemory_limit=-1 ./vendor/bin/phpunit -c config/phpunit.xml.dist

mutation-tests:                                                                 ## run mutation tests
	php -dmemory_limit=-1 ./vendor/bin/infection --configuration=config/infection.json.dist

code-coverage: unit-tests                                                       ## generate and upload test coverage metrics to https://coveralls.io/
	php -dmemory_limit=-1 ./vendor/bin/php-coveralls -x var/clover.xml -o var/coveralls-upload.json -v

docs-generate:                                                                  ## regenerate docs
	php docs/documenter.php

docs-check:                                                                     ## checks if docs are up to date
	php docs/documenter.php check

preload-check:                                                                  ## checks if preloader is configured correctly
	php src/preload.php

php-check:                                                                      ## shows which php binary is used
	which php

check: coding-standard-check static-analysis security-analysis unit-tests mutation-tests docs-check autoload-check  ## run quick checks for local development iterations
