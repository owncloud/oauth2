SHELL := /bin/bash

COMPOSER_BIN := $(shell command -v composer 2> /dev/null)
NPM := $(shell command -v npm 2> /dev/null)

NODE_PREFIX=$(shell pwd)

# bin file definitions
PHPUNIT=php -d zend.enable_gc=0 ../../lib/composer/bin/phpunit
PHPUNITDBG=phpdbg -qrr -d memory_limit=4096M -d zend.enable_gc=0 "../../lib/composer/bin/phpunit"
PHP_CS_FIXER=php -d zend.enable_gc=0 vendor-bin/owncloud-codestyle/vendor/bin/php-cs-fixer
PHP_CODESNIFFER=vendor-bin/php_codesniffer/vendor/bin/phpcs
PHAN=php -d zend.enable_gc=0 vendor-bin/phan/vendor/bin/phan
PHPSTAN=php -d zend.enable_gc=0 vendor-bin/phpstan/vendor/bin/phpstan
BEHAT_BIN=vendor-bin/behat/vendor/bin/behat

BOWER=$(NODE_PREFIX)/node_modules/bower/bin/bower
JSDOC=$(NODE_PREFIX)/node_modules/.bin/jsdoc

# Pinned rather than taken from $(notdir $(CURDIR)). This release line is built
# and signed by hand, often from a worktree, and the directory name then leaks into
# the signing key path, the staged directory and the tarball name: in a worktree
# called oauth2-063 the key lookup becomes ~/.owncloud/certificates/oauth2-063.key,
# CAN_SIGN silently goes false, and the package ships a top-level oauth2-063/ that
# does not match the app id. The id is fixed by appinfo/info.xml, so fix it here.
app_name=oauth2
doc_files=COPYING README.md CHANGELOG.md AUTHORS.md
src_dirs=appinfo css img js l10n lib templates vendor
all_src=$(src_dirs) $(doc_files)
build_dir=$(CURDIR)/build
dist_dir=$(build_dir)/dist

# internal aliases
composer_deps=vendor
composer_dev_deps=
acceptance_test_deps=vendor-bin/behat/vendor $(composer_deps)
nodejs_deps=
bower_deps=

occ=$(CURDIR)/../../occ
private_key=$(HOME)/.owncloud/certificates/$(app_name).key
certificate=$(HOME)/.owncloud/certificates/$(app_name).crt
sign=$(occ) integrity:sign-app --privateKey="$(private_key)" --certificate="$(certificate)"
sign_skip_msg="Skipping signing, either no key and certificate found in $(private_key) and $(certificate) or occ can not be found at $(occ)"
ifneq (,$(wildcard $(private_key)))
ifneq (,$(wildcard $(certificate)))
ifneq (,$(wildcard $(occ)))
	CAN_SIGN=true
endif
endif
endif

.DEFAULT_GOAL := help

# start with displaying help
help: ## Show this help message
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | sed -e 's/  */ /' | column -t -s :

.PHONY: clean
clean: ## Clean dependencies, builds
clean: clean-deps clean-dist clean-build

.PHONY: clean-composer-deps
clean-composer-deps:
	rm -Rf $(composer_deps) $(composer_dev_deps)
	rm -Rf vendor-bin/**/vendor vendor-bin/**/composer.lock

.PHONY: update-composer
update-composer: $(COMPOSER_BIN)
	rm -f composer.lock
	php $(COMPOSER_BIN) install --prefer-dist

#
# Node dependencies
#
$(nodejs_deps): package.json
	$(NPM) install --prefix $(NODE_PREFIX) && touch $@

$(BOWER): $(nodejs_deps)
$(JSDOC): $(nodejs_deps)

$(bower_deps): $(BOWER)
	$(BOWER) install && touch $@

#
# dist
#
$(dist_dir)/$(app_name): $(composer_deps) $(bower_deps)
	rm -Rf $@; mkdir -p $@
	cp -R $(all_src) $@
# appinfo/ is copied wholesale and signature.json is not gitignored, so one left
# behind in the source tree - by a stray `integrity:sign-app --path=.`, or by
# getting committed - would travel into the package. With no key present there is
# no re-sign to overwrite it, and it would then be packaged as a signature whose
# hashes describe a different tree.
	rm -f $@/appinfo/signature.json

ifdef CAN_SIGN
	$(sign) --path="$(dist_dir)/$(app_name)"
else
	@echo $(sign_skip_msg)
endif
# This branch targets ownCloud 10, whose integrity checker reads a single
# `certificate` and RSA/PSS only - it has no dispatch on the `v`/`alg` fields the
# current signature format adds. A package signed in that newer format fails
# integrity:check-app with "App Certificate is not valid" on every oc10 install,
# which is exactly what shipped as v0.6.2. Refuse to package one.
#
# This checks the envelope format, not whether the certificate chains to core's
# root - it cannot: `occ integrity:check-app` is useless here because
# Checker::isCodeCheckEnforced() returns false for the `git` channel, so any occ
# reachable from a build tree reports success unconditionally. The authoritative
# check is installing the built artifact into a real owncloud/server:10.16.x and
# running integrity:check-app there, which is part of cutting a release on this
# line. Classification matches core's own (v/certificates => current), and
# anything it cannot classify fails rather than passing.
#
# An unsigned build is tolerated by default, because that is dist.yml's normal
# output - it hands the reusable build workflow no signing secrets. Pass
# REQUIRE_SIGNATURE=1 to reject it, which is what cutting a release does: CAN_SIGN
# degrades to a printed message when the key, the cert or occ is missing, so
# without that the release path can hand back an unsigned tarball and exit 0.
#
# Which format is required is read from appinfo/info.xml's max-version rather than
# hardcoded, so this hunk is inert rather than harmful if it is ever merged or
# cherry-picked towards a branch targeting ownCloud 11. The version is parsed as
# XML, not by line-matching, so reformatting info.xml cannot silently disable the
# check - an unreadable max-version is an error, like an unclassifiable signature.
	@verdict=$$(php -d display_errors=0 -d error_reporting=0 -r '$$xml = @simplexml_load_file($$argv[1]); $$max = $$xml === false ? null : (string)($$xml->dependencies->owncloud["max-version"] ?? ""); if ($$max === null || $$max === "") { echo "noversion"; exit; } if (!\file_exists($$argv[2])) { echo "unsigned"; exit; } if ($$max !== "10" && \strpos($$max, "10.") !== 0) { echo "notoc10"; exit; } $$d = json_decode(file_get_contents($$argv[2]), true); if (!\is_array($$d)) { echo "unclassifiable"; } elseif (isset($$d["v"]) || isset($$d["certificates"])) { echo "current"; } elseif (isset($$d["certificate"], $$d["hashes"], $$d["signature"])) { echo "legacy"; } else { echo "unclassifiable"; }' appinfo/info.xml "$(dist_dir)/$(app_name)/appinfo/signature.json" 2>/dev/null); \
	case "$$verdict" in \
		legacy|notoc10) ;; \
		unsigned) case "$(REQUIRE_SIGNATURE)" in \
				''|0|no|false) ;; \
				*) echo "ERROR: REQUIRE_SIGNATURE was asked for but the package is unsigned."; \
					echo "       $(sign_skip_msg)"; exit 1;; \
			esac;; \
		current) echo "ERROR: appinfo/signature.json is in the current signature format, which ownCloud 10 cannot verify."; \
			echo "       Sign this release line with occ integrity:sign-app and its G1 key."; exit 1;; \
		noversion) echo "ERROR: could not read max-version from appinfo/info.xml, so the required"; \
			echo "       signature format is unknown. Refusing to package."; exit 1;; \
		'') echo "ERROR: php produced no verdict. Is php on PATH and built with simplexml?"; \
			echo "       Refusing to package without checking the signature."; exit 1;; \
		*) echo "ERROR: could not classify appinfo/signature.json (php said '$$verdict')."; \
			echo "       Refusing to package a signature that cannot be checked."; exit 1;; \
	esac
	tar -czf $(dist_dir)/$(app_name).tar.gz -C $(dist_dir) $(app_name)
	tar -cjf $(dist_dir)/$(app_name).tar.bz2 -C $(dist_dir) $(app_name)

.PHONY: dist
dist: ## Build distribution
dist: clean-dist $(dist_dir)/$(app_name)

.PHONY: clean-dist
clean-dist:
	rm -Rf $(dist_dir)

.PHONY: clean-build
clean-build:
	rm -Rf $(build_dir)

.PHONY: clean-deps
clean-deps: clean-composer-deps
	rm -Rf $(nodejs_deps) $(bower_deps)

##------------------------
## Tests
##------------------------
.PHONY: test-php-unit
test-php-unit: ## Run php unit tests
test-php-unit: $(composer_deps)
	$(PHPUNIT) --configuration ./phpunit.xml --testsuite unit

.PHONY: test-php-unit-dbg
test-php-unit-dbg: ## Run php unit tests using phpdbg
test-php-unit-dbg: $(composer_deps)
	$(PHPUNITDBG) --configuration ./phpunit.xml --testsuite unit

.PHONY: test-php-style
test-php-style: ## Run php-cs-fixer and check owncloud code-style
test-php-style: vendor-bin/owncloud-codestyle/vendor vendor-bin/php_codesniffer/vendor
	$(PHP_CS_FIXER) fix -v --diff --allow-risky yes --dry-run
	$(PHP_CODESNIFFER) --runtime-set ignore_warnings_on_exit --standard=phpcs.xml tests/acceptance

.PHONY: test-php-style-fix
test-php-style-fix: ## Run php-cs-fixer and fix code style issues
test-php-style-fix: vendor-bin/owncloud-codestyle/vendor
	$(PHP_CS_FIXER) fix -v --diff --allow-risky yes

.PHONY: test-php-phan
test-php-phan: ## Run phan
test-php-phan: vendor-bin/phan/vendor
	$(PHAN) --config-file .phan/config.php --require-config-exists

.PHONY: test-php-phpstan
test-php-phpstan: ## Run phpstan
test-php-phpstan: vendor-bin/phpstan/vendor
	$(PHPSTAN) analyse --memory-limit=4G --configuration=./phpstan.neon --no-progress --level=5 appinfo lib

.PHONY: test-acceptance-api
test-acceptance-api: ## Run API acceptance tests
test-acceptance-api: $(acceptance_test_deps)
	BEHAT_BIN=$(BEHAT_BIN) ../../tests/acceptance/run.sh --remote --type api

.PHONY: test-acceptance-cli
test-acceptance-cli: ## Run CLI acceptance tests
test-acceptance-cli: $(acceptance_test_deps)
	BEHAT_BIN=$(BEHAT_BIN) ../../tests/acceptance/run.sh --remote --type cli

.PHONY: test-acceptance-webui
test-acceptance-webui: ## Run webUI acceptance tests
test-acceptance-webui: $(acceptance_test_deps)
	BEHAT_BIN=$(BEHAT_BIN) ../../tests/acceptance/run.sh --remote --type webUI

#
# Dependency management
#--------------------------------------

composer.lock: composer.json
	@echo composer.lock is not up to date.

vendor: composer.lock
	$(COMPOSER_BIN) install --no-dev

vendor/bamarni/composer-bin-plugin: composer.lock
	$(COMPOSER_BIN) install

vendor-bin/owncloud-codestyle/vendor: vendor/bamarni/composer-bin-plugin vendor-bin/owncloud-codestyle/composer.lock
	$(COMPOSER_BIN) bin owncloud-codestyle install --no-progress

vendor-bin/owncloud-codestyle/composer.lock: vendor-bin/owncloud-codestyle/composer.json
	@echo owncloud-codestyle composer.lock is not up to date.

vendor-bin/php_codesniffer/vendor: vendor/bamarni/composer-bin-plugin vendor-bin/php_codesniffer/composer.lock
	composer bin php_codesniffer install --no-progress

vendor-bin/php_codesniffer/composer.lock: vendor-bin/php_codesniffer/composer.json
	@echo php_codesniffer composer.lock is not up to date.

vendor-bin/phan/vendor: vendor/bamarni/composer-bin-plugin vendor-bin/phan/composer.lock
	$(COMPOSER_BIN) bin phan install --no-progress

vendor-bin/phan/composer.lock: vendor-bin/phan/composer.json
	@echo phan composer.lock is not up to date.

vendor-bin/phpstan/vendor: vendor/bamarni/composer-bin-plugin vendor-bin/phpstan/composer.lock
	$(COMPOSER_BIN) bin phpstan install --no-progress

vendor-bin/phpstan/composer.lock: vendor-bin/phpstan/composer.json
	@echo phpstan composer.lock is not up to date.

vendor-bin/behat/vendor: vendor/bamarni/composer-bin-plugin vendor-bin/behat/composer.lock
	composer bin behat install --no-progress

vendor-bin/behat/composer.lock: vendor-bin/behat/composer.json
	@echo behat composer.lock is not up to date.
