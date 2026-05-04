# Changelog

The file documents changes to the PHP_CodeSniffer project for the 4.x series of releases.

## [4.0.1] - 2025-11-10

This release includes all improvements and bugfixes from PHP_CodeSniffer [3.13.5].

### Added
- Runtime support for PHP 8.5. All known PHP 8.5 deprecation notices have been fixed.
    - Syntax support for new PHP 8.5 features will follow in a future release.
    - If you find any PHP 8.5 deprecation notices which were missed, please report them.

### Changed
- The Squiz.ControlStructures.SwitchDeclaration sniff will now flag a PHP close tag as a "wrong opener" and will auto-fix this by inserting a colon. [#1316]
- Various housekeeping, including improvements to the tests and documentation.

### Fixed
- 4.x regression [#1277]: bring back whitespace tolerance in `phpcs:ignore` comma-separated rule reference lists.
    - Note: this bug did not affect `phpcs:disable`/`phpcs:enable` ignore annotations.
- Fixed bug [#968]: Generic.WhiteSpace.ScopeIndent was reporting false positives - and making incorrect fixes - for lines following a line containing an arrow function.
    - Thanks to [Soichi Sato][@Soh1121] for the patch.
- Fixed bug [#1216]: Tokenizer/PHP: added more defensive coding to prevent PHP 8.5 "Using null as an array offset" deprecation notices.
    - Thanks to [Andrew Lyons][@andrewnicols] for the patch.
- Fixed bug [#1279]: Tokenizer/PHP: on PHP < 8.0, an unclosed attribute (parse error) could end up removing some tokens from the token stream.
    - This could lead to false positives and false negative from sniffs, but could also lead to incorrect fixes being made mangling the file under scan.
- Fixed bug [#1315]: Squiz.ControlStructures.SwitchDeclaration: a number of the fixers would get into fixer conflicts with each other if the code under scan contained multiple statements on a line within a `switch`.
    - The sniff will now forbid - and auto-fix - multiple statements on one line for `case`/`default` and "case breaking" statements.
- Fixed bug [#1316]: Tokenizer/PHP: a PHP close tag after a `switch` case condition or after a `default` keyword, was not regarded as a "scope_opener" for the `case`/`default` body.
- Fixed bug [#1316]: PSR2.ControlStructures.SwitchDeclaration: the `WrongOpener` error is now also auto-fixable if the wrong opener is a PHP close tag.
- Fixed bug [#1316]: Squiz.PHP.NonExecutableCode would throw false positives when code within a switch control structure would move in and out of PHP.

[3.13.5]: https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/4.x/CHANGELOG-3.x.md#3135---2025-11-04

[#968]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/968
[#1216]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1216
[#1277]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1277
[#1279]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1279
[#1315]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1315
[#1316]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1316


## [4.0.0] - 2025-09-16

This release contains breaking changes.

Upgrade guides for both [ruleset maintainers/end-users][wiki-upgrade-4.0-end-users], as well as for [sniff developers and integrators][wiki-upgrade-4.0-extenders], have been published to the Wiki.

You are strongly encouraged to read the upgrade guide applicable to your situation before upgrading.

This release includes all improvements and bugfixes from PHP_CodeSniffer [4.0.0-beta1], [4.0.0-RC1], [3.13.3] and [3.13.4].

### Changed
- Tokenizer/PHP: fully qualified `exit`/`die`/`true`/`false`/`null` will be tokenized as the keyword token and the token `'content'` will include the leading backslash. [#1201]
- Wherever possible based on the PHP 7.2 minimum version, parameter types have been added to all methods. [#1237]
- The supported PHPUnit version constraints have been updated to `^8.4.0 || ^9.3.4 || ^10.5.32 || 11.3.3 - 11.5.28 || ^11.5.31`. [#1247]
    - External standards using the PHP_CodeSniffer native framework may need to update their own PHPUnit version constraints.
- Various housekeeping, including improvements to the tests and documentation.

### Fixed
- Fixed bug [#1082]: new exit codes weren't applied when running `phpcbf` on code provided via STDIN.
    - Thanks to [Dan Wallis][@fredden] for the patch.
- Fixed bug [#1172]: `// phpcs:set` for inline array properties did not handle a single item array with the value `true`, `false` or `null` correctly.
- Fixed bug [#1174]: progress bar wasn't showing files as fixed when running `phpcbf` in parallel mode.
- Fixed bug [#1226]: PHP 8.5 "Using null as an array offset" deprecation notice.

### Other
- Please be aware that the `master` branch has been renamed to `3.x` and the default branch has changed to the `4.x` branch.
    - If you contribute to PHP_CodeSniffer, you will need to update your local git clone.
    - If you develop against PHP_CodeSniffer and run your tests against dev branches of PHPCS, you will need to update your workflows.

[3.13.3]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/CHANGELOG-3.x.md#3133---2025-09-04
[3.13.4]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/CHANGELOG-3.x.md#3134---2025-09-05
[4.0.0-RC1]: https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/CHANGELOG-4.x.md#400rc1---2025-06-18

[#1082]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1082
[#1172]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1172
[#1174]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1174
[#1201]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1201
[#1226]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1226
[#1237]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1237
[#1247]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1247


## [4.0.0RC1] - 2025-06-18

This release includes all improvements and bugfixes from PHP_CodeSniffer [4.0.0-beta1], [3.13.1] and [3.13.2].

### Changed
- The error code `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterVisbility` has been changed to `Squiz.Functions.FunctionDeclarationArgumentSpacing.SpacingAfterVisibility`. [#1136]
- The following sniff(s) have received efficiency improvements:
    - Generic.ControlStructures.InlineControlStructure
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.

### Fixed
- Fixed bug [#3889][sq-3889] : A selective `phpcs:enable` could sometimes override a later selective `phpcs:ignore`.
    - Thanks to [Brad Jorsch][@anomiex] for the patch
- Fixed bug [#1128] : missing 'parenthesis_owner' index for T_FUNCTION token on PHP < 7.4 when function is named "fn".

### Other
- The GPG signature for the PHAR files has been rotated. The new fingerprint is: D91D86963AF3A29B6520462297B02DD8E5071466.

[3.13.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/CHANGELOG-3.x.md#3131---2025-06-13
[3.13.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/CHANGELOG-3.x.md#3132---2025-06-18
[4.0.0-beta1]: https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/CHANGELOG-4.x.md#400beta1---2025-05-11

[sq-3889]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3889
[#1128]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1128
[#1136]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1136


## [4.0.0beta1] - 2025-05-11

### Added
- Files without extension can now be scanned if the path is passed in directly. [#2916][sq-2916]
    - Previously, files without extension would always be ignored.
    - Now, files with no extension are checked if explicitly passed on the command line or specified in a ruleset.
    - Files without extension will still be ignored when scanning directories recursively.
- Support for extending a default value for an array property on a sniff from an XML ruleset file. [#15]
    - Previously, using `extend="true"` on an property tag for setting an array value could already _extend_ a property value set elsewhere in an (inluded) ruleset.
    - Now, you can also add to (extend) a default value as set on the sniff itself.
    - Note: the property default value and the values set via the ruleset will be merged.
        - This also means that for associative arrays, you can redefine the value for a particular array key.
        - For numerically indexed arrays, this means the array will be renumbered. Keep this in mind if the numeric indexes hold meaning.
- Added support for PHP 8.4 properties in interfaces to File::getMemberProperties(). [#2455][sq-2455]
    - Note: properties in interfaces is not fully supported yet, it is just this one method which handles them correctly at this moment.
- `Tokens::NAME_TOKENS` containing an array with the tokens used for identifier names. [#3041][sq-3041]
- New sniff `Generic.WhiteSpace.GotoTargetSpacing` to enforce no space between the label of a `goto` target and the colon following it. [#1026]
- An error message is now displayed if no files were checked during a run. [#1595][sq-1595]
    - This occurs when all of the specified files matched exclusion rules, or none matched filtering rules.
- An error will be shown when attempting to change an unchangable PHP ini setting using `-d option[=value]` or via the ruleset with `<ini name=...>`. [#416]
    - Previously, this was silently ignored.
    - Attempting to change non-existent ini settings (typo, extension not loaded) will continue to be silently ignored.

### Changed
- The minimum required PHP version has changed from 5.4.0 to 7.2.0.
- The default coding standard has changed from `PEAR` to `PSR12`.
- Both `phpcs` as well as `phpcbf` will now exit with exit code 0 if no issues were found/remain after fixing. [#184]
    - Non auto-fixable issues can be ignored for the exit code determination by setting the new `ignore_non_auto_fixable_on_exit` config flag to `1`.
    - For full details on the new exit codes, please refer to the Wiki ["Advanced Usage"](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Advanced-Usage#understanding-the-exit-codes) page.
- Composer installs no longer include any test files. [#1908][sq-1908]
    - The test framework files will still be included to allow for use by external standards.
- All status, debug, and progress output is now sent to STDERR instead of STDOUT. [#1612][sq-1612]
    - Only report output now goes through STDOUT. As a result of this, piping output to a file will now only include report output.
        - Pipe both STDERR and STDOUT to the same file to capture the entire output of the run.
    - The `--report-file` functionality remains untouched.
    - With this change in place, timing and memory consumption stats will now be displayed more often as this will no longer interfere with reports.
- The `--extensions` command line argument no longer accepts the tokenizer along with the extension. [#2448][sq-2448]
    - Previously, you would check `.module` files as PHP files using `--extensions=module/php`.
    - Now, you use `--extensions=module`.
- When processing rulesets, `<config>` directives will be applied based on the nesting level of the ruleset. [#2197][sq-2197]
    - Previously, it was not possible to overrule a `<config>` directive set in an included ruleset from the "root" ruleset.
    - Now, `<config>` directives set in the "root" ruleset will always "win" over directives in included rulesets.
    - When two included rulesets at the same nesting level both set the same directive, the value from the _last_ included ruleset "wins" (= same as before).
- When processing rulesets, `<arg>` directives will be applied based on the nesting level of the ruleset. [#2395][sq-2395], [#2597][sq-2597], [#2602][sq-2602]
    - Previously, it was not possible to overrule a `<arg>` directive set in an included ruleset from the "root" ruleset.
    - Now, `<arg>` directives set in the "root" ruleset will always "win" over directives in included rulesets.
    - When two included rulesets at the same nesting level both set the same directive, the value from the _first_ included ruleset "wins" (= same as before).
- Internal errors will no longer be suppressed when the `--sniffs` CLI argument is used. [#98]
- The `File::getDeclarationName()` method will no longer accept `T_ANON_CLASS` or `T_CLOSURE` tokens. [#3766][sq-3766]
    - A `RuntimeException` will be thrown if these tokens are passed.
- The `File::getDeclarationName()` method will now always return a string (or throw an Exception). [#1007]
    - Previously, the method would return `null` if the name could not be determined, like during live coding.
        Now it will return an empty string in those situations.
- The `File::getMemberProperties()` method will no longer add warnings about possible parse errors. [#2455][sq-2455]
    - This means the `Internal.ParseError.InterfaceHasMemberVar` and the `Internal.ParseError.EnumHasMemberVar` error codes have been removed.
    - The method will now throw a "$stackPtr is not a class member var" `RuntimeException` for properties declared in enums (parse error).
    - Properties declared in interfaces will be analyzed like all other properties, as these are allowed since PHP 8.4.
- None of the included sniffs will warn about possible parse errors any more. [#2455][sq-2455]
    - This improves the experience when the file is being checked inside an editor during live coding.
    - If you want to detect parse errors, use the `Generic.PHP.Syntax` sniff or a dedicated linter instead.
- The PEAR + PSR2 FunctionCallSignature sniffs will now also examine anonymous class instantiations with parameters. [#47]
- The error code `Squiz.Classes.ValidClassName.NotCamelCaps` has been changed to `Squiz.Classes.ValidClassName.NotPascalCase`. [#2046][sq-2046]
    - This reflects that the sniff is actually checking for `ClassName` and not `className`.
- The error code `Squiz.PHP.Heredoc.NotAllowed` has been replaced by `Squiz.PHP.Heredoc.HeredocNotAllowed` and `Squiz.PHP.Heredoc.NowdocNotAllowed`. [#2318][sq-2318]
    - This allows for forbidding either heredocs or nowdocs without forbidding both.
- The `PSR12.Files.FileHeader` sniff now has more modular error codes to allow for more selectively applying the rules. [#2729][sq-2729] [#3453][sq-3453]
    - The `PSR12.Files.FileHeader.SpacingAfterBlock` error code is replaced by:
        - `PSR12.Files.FileHeader.SpacingAfterTagBlock`
        - `PSR12.Files.FileHeader.SpacingAfterDocblockBlock`
        - `PSR12.Files.FileHeader.SpacingAfterDeclareBlock`
        - `PSR12.Files.FileHeader.SpacingAfterNamespaceBlock`
        - `PSR12.Files.FileHeader.SpacingAfterUseBlock`
        - `PSR12.Files.FileHeader.SpacingAfterUseFunctionBlock`
        - `PSR12.Files.FileHeader.SpacingAfterUseConstBlock`
    - The `PSR12.Files.FileHeader.SpacingInsideBlock` error code is replaced by:
        - `PSR12.Files.FileHeader.SpacingInsideUseBlock`
        - `PSR12.Files.FileHeader.SpacingInsideUseFunctionBlock`
        - `PSR12.Files.FileHeader.SpacingInsideUseConstBlock`
- The error code `Squiz.Commenting.VariableComment.TagNotAllowed` has been replaced by a dynamic `Squiz.Commenting.VariableComment.[TagName]TagNotAllowed` error code. [#1039]
    - This allows for selectively allowing specific tags by excluding the error code for that tag.
    - Example: to allow `@link` tags in property docblocks, exclude the `Squiz.Commenting.VariableComment.LinkTagNotAllowed` error code.
- The following sniffs have received performance related improvements:
    - PEAR.NamingConventions.ValidVariableName
    - PSR2.Classes.PropertyDeclaration
    - Squiz.Commenting.VariableComment
    - Squiz.Scope.MemberVarScope
    - Squiz.WhiteSpace.MemberVarSpacing
    - These sniffs will no longer listen to non-variable tokens, nor for variables tokens outside of OO context. [#374]
        External sniffs which extend one of these sniffs may need adjustment if they want to retain the old behaviour.
- PHPCS now uses the PHP >= 8.0 native method for tokenizing (namespaced) identifier names. [#3041][sq-3041]
    - Before PHP 8.0, PHP would tokenize namespaced names using `T_STRING` and `T_NS_SEPARATOR`.
    - From PHP 8.0, PHP uses the tokens `T_NAME_FULLY_QUALIFIED`, `T_NAME_RELATIVE`, and `T_NAME_QUALIFIED` instead.
    - PHPCS now uses these new PHP 8.0 tokens no matter what version of PHP is being used to run PHPCS.
    - Custom sniffs that use `T_STRING` and `T_NS_SEPARATOR` tokens to look for namespaced names will need to be modified.
    - The `Tokens::FUNCTION_NAME_TOKENS`/`Tokens::$functionNameTokens` array now includes the identifier name tokens.
- Closure T_USE tokens, T_ISSET, T_UNSET, T_EMPTY, T_EVAL and T_EXIT tokens now contain parenthesis information. [#23], [#2593][sq-2593]
    - Previously, you had to find the opening and closing parenthesis by looking forward through the token stack.
    - Now, you can use the `parenthesis_owner`, `parenthesis_opener` and `parenthesis_closer` array indexes.
- The `static` keyword when preceded by `instanceof` will now be tokenized (again) as `T_STATIC`. [#22]
    - Previously, the token was (re-)tokenized to `T_STRING`.
- `T_OPEN_TAG` tokens will no longer contain any whitespace. [#593]
    - Previously, "long" open tags could include either a single space or a new line character.
    - This whitespace will now be tokenized as a `T_WHITESPACE` token.
- `T_GOTO_LABEL` tokens will no longer include the colon following it. [#185]
    - The colon belonging with a goto label will now be tokenized separately as `T_GOTO_COLON`.
- Context sensitive keywords used as a label in a `goto` statement will now be tokenized as `T_STRING` to prevent confusing sniffs. [#185]
- All `T_DOC_COMMENT_*` tokens now have the `comment_opener` and `comment_closer` indexes set. [#484]
- The `Tokens::FUNCTION_NAME_TOKENS`/`Tokens::$functionNameTokens` array now includes the `T_ANON_CLASS` token. [#47]
- Type casting for sniff property values set from within a ruleset has been made more consistent. [#708]
    - `true` and `false` will now always be set to a boolean value, independently of the case in which the value was provided.
    - `null` will now be set to an actual `null` value. Previously, the sniff property would have been set to string `'null'`.
    - Array element values will now also get the type casting treatment. Previously, array values would always be strings.
- The `PHP_CodeSniffer\Config::setConfigData()` method is no longer static. [#2675][sq-2675]
    - The associated (`private`) `Config::$overriddenDefaults` property is also no longer static.
- The `PHP_CodeSniffer\Config::setSettings()` method is now a `void` method. [#1001]
- The signature of the `DummyFile::setErrorCounts()` method has changed and now expects the following parameters: `$errorCount, $warningCount, $fixableErrorCount, $fixableWarningCount, $fixedErrorCount, $fixedWarningCount`. [#1079]
- The `Generator` classes will now throw a `PHP_CodeSniffer\Exceptions\GeneratorException` when encountering errors in the documentation XML. [#1072]
- The `PHP_CodeSniffer\Generators\HTML::STYLESHEET`, `PHP_CodeSniffer\Util\Timing::MINUTE_IN_MS` and `PHP_CodeSniffer\Util\Timing::SECOND_IN_MS` class constants are no longer `public`. [#1051]
- The `PHP_CodeSniffer\Util\Timing` class is now `final` and marked as an internal class. [#1074]
- The Ruleset class no longer has special behaviour when used in a test context. [#996]
- The minimum required PHPUnit version for the test framework has changed from 4.0 to 8.0. [#994], [#997]
    - The test framework is now compatible with PHPUnit 8.x - 11.x (ignoring PHPUnit deprecations related to PHPUnit 12).
- The test framework has been refactored and no longer creates a custom test suite. [#25]
    - If tests for an external standard extend the PHPCS native test suite, be sure to read the upgrade guide for more detail.
- The two abstract base test cases have been renamed. [#25]
    - Replace `PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest` with `PHP_CodeSniffer\Tests\Standards\AbstractSniffTestCase`.
    - Replace `PHP_CodeSniffer\Tests\Core\AbstractMethodUnitTest` with `PHP_CodeSniffer\Tests\Core\AbstractMethodTestCase`.
- Tests which extend the `AbstractSniffTestCase` for which no test case files (`.inc` files) can be found, will now be marked as "incomplete". [#998]
    - Previously, those tests would silently pass.
- All test case files (`inc`) which would be changed by the sniff under test if running the fixer, are now required to be accompanied by a `*.fixed` file. [#300]

### Deprecated

- The static token array properties in the `Tokens` class. Use the corresponding class constants on the Tokens class instead. [#500]
- `PHP_CodeSniffer\Util\Common::$allowedTypes`. Use `PHP_CodeSniffer\Util\Common::ALLOWED_TYPES` instead. [#1043]
- `PHP_CodeSniffer\Tokenizers\PHP::$tstringContexts`. Use `PHP_CodeSniffer\Tokenizers\PHP::T_STRING_CONTEXTS` instead. [#1043]
- `PHP_CodeSniffer\Sniffs\AbstractVariableSniff::$phpReservedVars`. Use `PHP_CodeSniffer\Sniffs\AbstractVariableSniff::PHP_RESERVED_VARS` instead. [#1043]
- `PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff::$magicMethods`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff::MAGIC_METHODS` instead. [#1043]
    - This also affects the `PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff` class which extends the `CamelCapsFunctionNameSniff`.
- `PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff::$methodsDoubleUnderscore`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff::DOUBLE_UNDERSCORE_METHODS` instead. [#1043]
    - This also affects the `PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff` class which extends the `CamelCapsFunctionNameSniff`.
- `PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff::$magicFunctions`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff::MAGIC_FUNCTIONS` instead. [#1043]
    - This also affects the `PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff` class which extends the `CamelCapsFunctionNameSniff`.
- `PHP_CodeSniffer\Standards\Generic\Sniffs\Files\ByteOrderMarkSniff::$bomDefinitions`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\Files\ByteOrderMarkSniff::BOM_DEFINITIONS` instead. [#1043]
- `PHP_CodeSniffer\Standards\Generic\Sniffs\Files\InlineHTMLSniff::$bomDefinitions`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\Files\InlineHTMLSniff::BOM_DEFINITIONS` instead. [#1043]
- `PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\CharacterBeforePHPOpeningTagSniff::$bomDefinitions`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\CharacterBeforePHPOpeningTagSniff::BOM_DEFINITIONS` instead. [#1043]
- `PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl\SubversionPropertiesSniff::$properties`. Use `PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl\SubversionPropertiesSniff::REQUIRED_PROPERTIES` instead. [#1043]
- `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff::$tags`. Use `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff::EXPECTED_TAGS` instead. [#1043]
    - This also affects the `PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff` class which extends the `FileCommentSniff`.
- `PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidFunctionNameSniff::$magicMethods`. Use `PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidFunctionNameSniff::MAGIC_METHODS` instead. [#1043]
    - This also affects the `PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidFunctionNameSniff` class which extends the PEAR `ValidFunctionNameSniff`.
- `PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidFunctionNameSniff::$magicFunctions`. Use `PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions\ValidFunctionNameSniff::MAGIC_FUNCTIONS` instead. [#1043]
    - This also affects the `PHP_CodeSniffer\Standards\Squiz\Sniffs\NamingConventions\ValidFunctionNameSniff` class which extends the PEAR `ValidFunctionNameSniff`.
- `PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\DisallowSizeFunctionsInLoopsSniff::$forbiddenFunctions`. Use `PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP\DisallowSizeFunctionsInLoopsSniff::FORBIDDEN_FUNCTIONS` instead. [#1043]
- `PHP_CodeSniffer\Util\Standards::printInstalledStandards()`. Use `echo PHP_CodeSniffer\Util\Standards::prepareInstalledStandardsForDisplay()` instead. [#1054]
- `PHP_CodeSniffer\Config::printConfigData()`. Use `echo PHP_CodeSniffer\Config::prepareConfigDataForDisplay()` instead. [#1055]
- The `Reporter::$totalFixable` and `Reporter::$totalFixed` properties. [#1079]
    - Use respectively `(Reporter::$totalFixableErrors + Reporter::$totalFixableWarnings)` and `(Reporter::$totalFixedErrors + Reporter::$totalFixedWarnings)` instead.

### Removed
- Support for checking the coding standards of CSS and JS files has been removed. [#2448][sq-2448]
    - Sniffs which are specifically aimed at CSS/JS files will no longer run.
    - All other sniffs will now treat all files under scan as PHP files.
    - The JS/CSS tokenizers and all tokens which were specifically for CSS/JS have also been removed.
- Support for sniffs not implementing the `PHP_CodeSniffer\Sniffs\Sniff` interface. See [#694].
- Support for sniffs not following the naming conventions. See [#689].
- Support for external standards called "Internal". See [#799].
- Support for the deprecated `@codingStandard` annotation syntax has been removed. [#1954][sq-1954]
    - Use the `phpcs:` or `@phpcs:` syntax instead
        - Replace `@codingStandardsIgnoreFile` with `phpcs:ignoreFile`
        - Replace `@codingStandardsIgnoreStart` with `phpcs:disable`
        - Replace `@codingStandardsIgnoreEnd` with `phpcs:enable`
        - Replace `@codingStandardsIgnoreLine` with `phpcs:ignore`
        - Replace `@codingStandardsChangeSetting` with `phpcs:set`
- Support for the deprecated `ruleset.xml` array property string-based syntax has been removed. [#1983][sq-1983]
    - Previously, an array value could be set using a comma-delimited string `print=>echo,create_function=>null`
    - Now, individual array elements are specified using an `element` tag with `key` and `value` attributes
        - For example, `<element key="print" value="echo">`
- The unused `T_ARRAY_HINT` token. [#1997][sq-1997]
- The unused `T_RETURN_TYPE` token. [#1997][sq-1997]
- The `Generic.Debug.ClosureLinter` sniff. [#2448][sq-2448]
- The `Generic.Debug.CSSLint` sniff. [#2448][sq-2448]
- The `Generic.Debug.ESLint` sniff. [#2448][sq-2448]
- The `Generic.Debug.JSHint` sniff. [#2448][sq-2448]
- The `Generic.Formatting.NoSpaceAfterCast` sniff. [#2234][sq-2234]
    - Use the `Generic.Formatting.SpaceAfterCast` sniff instead with the `$spacing` property set to `0`.
- The `Generic.Functions.CallTimePassByReference` sniff. [#921]
- The entire `MySource` standard, and all sniffs within. [#2471][sq-2471]
- The `Squiz.Classes.DuplicateProperty` sniff. [#2448][sq-2448]
- The entire `Squiz.CSS` category, and all sniffs within. [#2448][sq-2448]
- The `Squiz.Debug.JavaScriptLint` sniff. [#2448][sq-2448]
- The `Squiz.Debug.JSLint` sniff. [#2448][sq-2448]
- The `Squiz.Objects.DisallowObjectStringIndex` sniff. [#2448][sq-2448]
- The `Squiz.Objects.ObjectMemberComment` sniff. [#2448][sq-2448]
- The `Squiz.WhiteSpace.LanguageConstructSpacing` sniff. [#1953][sq-1953]
    - Use the `Generic.WhiteSpace.LanguageConstructSpacing` sniff instead.
- The `Squiz.WhiteSpace.PropertyLabelSpacing` sniff. [#2448][sq-2448]
- The `Zend.Debug.CodeAnalyzer` sniff. [#58]
- The `error` property of sniff `Generic.Strings.UnnecessaryStringConcat`. See [#2823][sq-2823]
    - This sniff now always produces errors
    - To make this sniff produce warnings, include the following in a `ruleset.xml` or `[.]phpcs.xml[.dist]` file:
        ```xml
        <rule ref="Generic.Strings.UnnecessaryStringConcat">
            <type>warning</type>
        </rule>
        ```
- The `error` property of sniff `Generic.Formatting.MultipleStatementAlignment`. See [#2823][sq-2823]
    - This sniff now always produces warnings
    - The `Generic.Formatting.MultipleStatementAlignment.IncorrectWarning` error code has been removed.
        - Refer to the `Generic.Formatting.MultipleStatementAlignment.Incorrect` error code instead.
    - The `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` error code has been removed.
        - Refer to the `Generic.Formatting.MultipleStatementAlignment.NotSame` error code instead.
    - To make this sniff produce errors, include the following in a `ruleset.xml` or `[.]phpcs.xml[.dist]` file:
        ```xml
        <rule ref="Generic.Formatting.MultipleStatementAlignment">
            <type>error</type>
        </rule>
        ```
- The `$ignoreComments` parameter for the `AbstractPatternSniff::__construct()` method.
- Ruleset::setSniffProperty(): support for the old `$settings` parameter format. [#3629][sq-3629]
- Use of the deprecated `auto_detect_line_endings` ini setting. [#3394][sq-3394]
    - This removes support for files with `\r` line endings.
- The abstract `PHP_CodeSniffer\Filters\ExactMatch::getBlacklist()` and `PHP_CodeSniffer\Filters\ExactMatch::getWhitelist()` methods. See [#199].
    - These have been replaced by the `ExactMatch::getDisallowedFiles()` and `ExactMatch::getAllowedFiles()` methods.
- The deprecated `PHP_CodeSniffer\Generators\[HTML|Markdown|Text]::print*()` methods. See [#755].
- Unused static `PHP_CodeSniffer\Reporter::$startTime` property. [#1064]

### Fixed
- Fixed bug [#185] : goto labels were incorrectly tokenized as `T_STRING` if there was whitespace and/or comments between the label and colon.
- Fixed bug [#1012] : in edge cases, the tokenizer could create some stray `parenthesis_*` keys.
- Fixed bug [#1020] : File::findExtendedClassName() will no longer break on namespace relative class names.
- Fixed bug [#1020] : File::findImplementedInterfaceNames() will no longer break on namespace relative interface names.
- Fixed bug [#1020] : Various sniffs now have better support for ignoring/examining qualified function calls.

### Other
**Calling all testers!**

Please help by testing the beta release and reporting any issues you run into.
Upgrade guides for both [ruleset maintainers/end-users][wiki-upgrade-4.0-end-users], as well as for [sniff developers and integrators][wiki-upgrade-4.0-extenders], have been published to the Wiki to help smooth the transition.

All patches courtesy of [Greg Sherwood][@gsherwood] and [Juliette Reinders Folmer][@jrfnl].

Special thanks go out to [Dan Wallis][@fredden] and [Rodrigo Primo][@rodrigoprimo] for their reviews and feedback.

[wiki-upgrade-4.0-end-users]: https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Version-4.0-User-Upgrade-Guide
[wiki-upgrade-4.0-extenders]: https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Version-4.0-Developer-Upgrade-Guide

[sq-1595]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1595
[sq-1612]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1612
[sq-1908]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1908
[sq-1953]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1953
[sq-1954]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1954
[sq-1983]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1983
[sq-1997]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1997
[sq-2046]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2046
[sq-2197]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2197
[sq-2234]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2234
[sq-2318]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2318
[sq-2395]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2395
[sq-2448]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2448
[sq-2455]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2455
[sq-2471]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2471
[sq-2593]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2593
[sq-2597]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2597
[sq-2602]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2602
[sq-2675]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2675
[sq-2729]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2729
[sq-2823]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2823
[sq-2916]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2916
[sq-3041]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3041
[sq-3394]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3394
[sq-3453]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3453
[sq-3629]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3629
[sq-3766]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3766

[#15]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/15
[#22]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/22
[#23]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/23
[#25]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/25
[#47]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/47
[#58]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/58
[#98]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/98
[#184]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/184
[#185]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/185
[#199]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/199
[#300]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/300
[#374]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/374
[#416]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/416
[#484]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/484
[#500]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/500
[#593]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/593
[#689]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/689
[#694]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/694
[#708]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/708
[#755]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/755
[#799]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/799
[#921]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/921
[#994]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/994
[#996]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/996
[#997]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/997
[#998]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/998
[#1001]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1001
[#1007]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1007
[#1012]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1012
[#1020]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1020
[#1026]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1026
[#1039]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1039
[#1043]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1043
[#1051]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1051
[#1054]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1054
[#1055]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1055
[#1064]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1064
[#1072]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1072
[#1074]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1074
[#1079]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1079


<!--
=== Link list for release links ====
-->

[4.0.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/4.0.0...4.0.1
[4.0.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/4.0.0RC1...4.0.0
[4.0.0RC1]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/4.0.0beta1...4.0.0RC1
[4.0.0beta1]: https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.13.0...4.0.0beta1


<!--
=== Link list for contributor profiles ====
-->

[@andrewnicols]:        https://github.com/andrewnicols
[@anomiex]:             https://github.com/anomiex
[@fredden]:             https://github.com/fredden
[@gsherwood]:           https://github.com/gsherwood
[@jrfnl]:               https://github.com/jrfnl
[@rodrigoprimo]:        https://github.com/rodrigoprimo
[@Soh1121]:             https://github.com/Soh1121
