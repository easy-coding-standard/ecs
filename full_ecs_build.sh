#!/usr/bin/env bash

# add patches
composer install --ansi

# but skip dev dependencies
composer update --no-dev --ansi

# remove tests and useless files, to make downgraded, scoped and deployed codebase as small as possible
rm -rf tests vendor/phpcsstandards/php_codesniffer/tests vendor/phpcsstandards/php_codesniffer/src/Standards/Generic/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/MySource/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/PEAR/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/PSR1/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/PSR12/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/PSR2/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/Squiz/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/Zend/Tests vendor/phpcsstandards/php_codesniffer/src/Standards/Generic/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/MySource/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/PEAR/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/PSR1/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/PSR12/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/PSR2/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/Squiz/Docs vendor/phpcsstandards/php_codesniffer/src/Standards/Zend/Docs vendor/phpcsstandards/php_codesniffer/src/Reports vendor/phpcsstandards/php_codesniffer/src/Filters vendor/phpcsstandards/php_codesniffer/src/Generators vendor/friendsofphp/php-cs-fixer/src/Linter vendor/friendsofphp/php-cs-fixer/src/Runner vendor/friendsofphp/php-cs-fixer/src/Documentation vendor/friendsofphp/php-cs-fixer/src/Cache vendor/friendsofphp/php-cs-fixer/src/Console/Output vendor/friendsofphp/php-cs-fixer/src/Console/Report vendor/friendsofphp/php-cs-fixer/src/Console/SelfUpdate vendor/friendsofphp/php-cs-fixer/src/Console/Application.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/DescribeCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/Documentation.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/FixCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/HelpCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/ListSetsCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/SelfUpdateCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/ListFilesCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/DocumentationCommand.php vendor/friendsofphp/php-cs-fixer/src/Console/Command/WorkerCommand.php vendor/friendsofphp/php-cs-fixer/src/Runner/Runner.php

# downgrade with rector
mkdir rector-local
composer require rector/rector --working-dir rector-local --dev
rector-local/vendor/bin/rector process bin config/config.php src vendor --config build/rector-downgrade-php-72.php --ansi

# prefix
sh prefix-code.sh
