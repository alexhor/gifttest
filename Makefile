app_name=gifttest

project_dir=$(CURDIR)
build_dir=$(CURDIR)/build
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
sign_dir=$(build_dir)/sign
version+=0.0.1


all: dev-setup lint stylelint build-js-production test

release: npm-init build-js-production lint stylelint appstore

# Dev env management
dev-setup: clean clean-dev npm-init phplint-init

npm-init:
	npm install

npm-update:
	npm update

phplint-init:
	composer create-project wp-coding-standards/wpcs --no-dev
	./wpcs/vendor/bin/phpcs --config-set installed_paths wpcs/

todo:
	grep -r --exclude-dir build --exclude-dir js --exclude-dir node_modules --exclude-dir .git "TODO"

# Building
build-js:
	npm run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

# Testing
test:
	npm run test

test-watch:
	npm run test:watch

test-coverage:
	npm run test:coverage

# Linting
phplint:
	./wpcs/vendor/bin/phpcs --standard=WordPress --colors --extensions=php -p class $(app_name).php uninstall.php

phplint-fix:
	./wpcs/vendor/bin/phpcbf --standard=WordPress --colors --extensions=php -p class $(app_name).php uninstall.php

# Style linting
stylelint:
	npm run stylelint "**/*.css"

stylelint-fix:
	npm run stylelint:fix "**/*.css"

# Cleaning
clean:
	rm -f js/$(app_name)_*.js
	rm -f js/$(app_name)_*.js.map

clean-dev:
	rm -rf node_modules

create-tag:
	git tag -a v$(version) -m "Tagging the $(version) release."
	git push origin v$(version)

appstore:
	rm -rf $(build_dir)
	mkdir -p $(build_dir)
	rsync -a \
	--exclude=.git \
	--exclude=build \
	--exclude=node_modules \
	--exclude=.eslintrc.js \
	--exclude=.stylelintrc.js \
	--exclude=.gitignore \
	--exclude=Makefile \
	--exclude=package.json \
	--exclude=package-lock.json \
	--exclude=webpack.common.js \
	--exclude=webpack.dev.js \
	--exclude=webpack.prod.js \
	--exclude=js/**.js.map \
	--exclude=README.md \
	--exclude=src \
	--exclude=wpcs \
	--exclude=lang/**.po \
	$(project_dir)/  $(build_dir)/$(app_name)
	tar -czf $(build_dir)/$(app_name)-$(version).tar.gz \
		-C $(build_dir) $(app_name)
