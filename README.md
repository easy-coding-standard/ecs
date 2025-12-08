# The Easiest way to use Coding Standard

[![Downloads total](https://img.shields.io/packagist/dt/symplify/easy-coding-standard.svg?style=flat-square)](https://packagist.org/packages/symplify/easy-coding-standard/stats)

<br>

## Killer Features

- Install on **any PHP 7.2-PHP 8.5** project with any dependencies
- Blazing fast with parallel run out of the box
- Use [PHP_CodeSniffer or PHP-CS-Fixer](https://tomasvotruba.com/blog/2017/05/03/combine-power-of-php-code-sniffer-and-php-cs-fixer-in-3-lines/) - anything you like
- Use **prepared sets** and [PHP CS Fixer sets](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst) to save time

<br>

## Install

```bash
composer require symplify/easy-coding-standard --dev
```

<br>

## Usage

```bash
vendor/bin/ecs
```

On the first run, ECS creates `ecs.php` config file with directories and first rule to kick off.

Then you can run again to see the suggested diffs:

```bash
vendor/bin/ecs
```

To actually **fix your code**, add `--fix`:

```bash
vendor/bin/ecs --fix
```

That's it!

<br>

## Configure

Most of the time, you'll be happy with the default configuration. The most relevant part is configuring paths, checkers and sets:

```php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withConfiguredRule(
        ArraySyntaxFixer::class,
        ['syntax' => 'long']
    )
    ->withRules([
        ListSyntaxFixer::class,
    ])
    ->withPreparedSets(psr12: true);
```

<br>

Do you want to check all `*.php` files in your root (`ecs.php`, `rector.php` etc.)? Instead of listing them one by one, use `->withRootFiles()` method:

```php
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withRootFiles();
```

<br>

Do you want to include one of sets from [php-cs-fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst)?

You can:

```php
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withPhpCsFixerSets(perCS20: true, doctrineAnnotation: true);
```

<br>

### How to Skip Files/Rules?

Love the sets of rules, but want to skip single rule or some files?

```php
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSkip([
        // skip single rule
        ArraySyntaxFixer::class,

        // skip single rule in specific paths
        ArraySyntaxFixer::class => [
            __DIR__ . '/src/ValueObject/',
        ],

        // skip directory by absolute or * mask
        __DIR__ . '/src/Migrations',

        // skip directories by mask
        __DIR__ . '/src/*/Legacy',
    ]);
```

<br>

### Less Common Options

You probably won't use these, but they can give you more control over the internal process:

```php
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return ECSConfig::configure()
    // file extensions to scan
    ->withFileExtensions(['php'])

    // configure cache paths and namespace - useful e.g. Gitlab CI caching, where getcwd() produces always different path
    ->withCache(
        directory: sys_get_temp_dir() . '/_changed_files_detector_tests',
        namespace: getcwd() // normalized to directory separator
    )

    // print contents with specific indent rules
    ->withSpacing(indentation: Option::INDENTATION_SPACES, lineEnding: PHP_EOL)

    // modify parallel run
    ->withParallel(timeoutSeconds: 120, maxNumberOfProcess: 32, jobSize: 20);
```

Mentioned values are default ones.

<br>

Do you use ECS across variety of project? Do you want to run them always the same way in each of those project? Let's make use of [Composer scripts](https://blog.martinhujer.cz/have-you-tried-composer-scripts/)

This command adds 2 handy scripts to your `composer.json`:

```bash
vendor/bin/ecs scripts
```

Run them always the same way - to check the code:

```bash
composer check-cs
```

To apply fixes, run:

```bash
composer fix-cs
```

<br>

### Controlling Output Format

You may want to use ECS to generate reports for third-party tooling.

We currently provide formatters for:

- `console`:  Human-oriented printing à la PHP CS Fixer.
- `json`:  A custom JSON blob for arbitrary tooling.
- `junit`:  JUnit format to be used in different CI environments.
- `checkstyle`: Useful for Github Action Reports.
- `gitlab`: For Gitlab code quality reports or Code Climate tooling.

You can use the output format option as below

```bash
vendor/bin/ecs --output-format=checkstyle
```

For information on how each of these behave, refer to their respective
[implementations](src/Console/Output/).

<br>

## FAQ

### How do I clear cache?

```bash
vendor/bin/ecs --clear-cache
```

### How can I see all used rules?

```bash
vendor/bin/ecs list-checkers
```

Do you look for json format?

```bash
vendor/bin/ecs list-checkers --output-format json
```

<br>

### Can I Use My [`.editorconfig`](https://editorconfig.org/)?

Mostly! By using `withEditorConfig()`, ECS will automatically discover
the `.editorconfig` file in the project's root directory. It will use any
rules under `[*]` or `[*.php]` (the latter taking priority) and respect the
settings for:

-   `indent_style`
-   `end_of_line`
-   `max_line_length`
-   `trim_trailing_whitespace`
-   `insert_final_newline`
-   [`quote_type`](https://github.com/jednano/codepainter#quote_type-single-double-auto)
    -   Only `single` and `auto` are respected.
    -   Warning: this is a proposed field, but not fully standard.

These settings will take precedence over similar rules configured through sets
like PSR12, to avoid conflicting with other tooling using your `.editorconfig`.

Unfortunately, not all settings are currently respected, but PRs are always
welcome!

## How to Migrate from another coding standard tool?

Do you use another tool and want to migrate? It's pretty straightforward - here is "how to":

* for [PHP_CodeSniffer](https://tomasvotruba.com/blog/2018/06/04/how-to-migrate-from-php-code-sniffer-to-easy-coding-standard)
* and [PHP CS Fixer](https://tomasvotruba.com/blog/2018/06/07/how-to-migrate-from-php-cs-fixer-to-easy-coding-standard).
