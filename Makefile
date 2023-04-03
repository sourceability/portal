.PHONY: help
.DEFAULT_GOAL := help
SHELL = /bin/bash

# https://stackoverflow.com/a/23324703
ROOT_DIR := $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

ifneq (, $(shell which docker))
	EXEC_PHP ?= docker-compose run php
else
	EXEC_PHP ?=
endif

help:
	 @sed -E '$$!N;s/^## (.+)\n([^ :]+):.*$$/\2: ## \1/p;D' $(MAKEFILE_LIST) \
		| sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m#####%s\n", $$1, $$2}' \
		| column -t -s '#####'

.PHONY: php
## Open a PHP shell
php: vendor .env
	$(EXEC_PHP) bash

.PHONY: ecs
## Fix code with Easy Coding Standard
ecs: vendor .env
	$(EXEC_PHP) vendor/bin/ecs --fix

.PHONY: rector
## Fix code with Rector
rector: vendor .env
	$(EXEC_PHP) vendor/bin/rector

.PHONY: phpstan
## Run static analysis
phpstan: vendor .env
	$(EXEC_PHP) vendor/bin/phpstan

.PHONY: phpunit
## Run tests
phpunit: vendor .env
	$(EXEC_PHP) vendor/bin/phpunit

.PHONY: pre-commit
## Useful targets to run before committing
pre-commit: ecs rector phpstan phpunit

.env:
	@read -sp 'Enter your OpenAI API Key (to save in gitignore .env): ' OPENAI_API_KEY ; \
	echo "OPENAI_API_KEY=$${OPENAI_API_KEY}" > .env

COMPOSER_FILES := $(wildcard $(ROOT_DIR)/composer.*)

vendor: $(COMPOSER_FILES)
	$(EXEC_PHP) composer install
	@touch $@ # Force directory mtime refresh
