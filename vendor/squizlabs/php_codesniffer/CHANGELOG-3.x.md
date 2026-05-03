# Changelog

The file documents changes to the PHP_CodeSniffer project for the 3.x series of releases.

## [3.13.5] - 2025-11-04

### Added
- Runtime support for PHP 8.5. All known PHP 8.5 deprecation notices have been fixed.
    - Syntax support for new PHP 8.5 features will follow in a future release.
    - If you find any PHP 8.5 deprecation notices which were missed, please report them.

### Changed
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#1216]: Tokenizer/PHP: added more defensive coding to prevent PHP 8.5 "Using null as an array offset" deprecation notices.
    - Thanks to [Andrew Lyons][@andrewnicols] for the patch.
- Fixed bug [#1279]: Tokenizer/PHP: on PHP < 8.0, an unclosed attribute (parse error) could end up removing some tokens from the token stream.
    - This could lead to false positives and false negative from sniffs, but could also lead to incorrect fixes being made mangling the file under scan.
    - Thanks to [Juliette Reinders Folmer](https://github.com/jrfnl) for the patch.

### Other
- Please be aware that the `master` branch has been renamed to `3.x` and the default branch has changed to the `4.x` branch.
    - If you contribute to PHP_CodeSniffer, you will need to update your local git clone.
    - If you develop against PHP_CodeSniffer and run your tests against dev branches of PHPCS, you will need to update your workflows.

[#1216]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1216
[#1279]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1279


## [3.13.4] - 2025-09-05

### Fixed
- Fixed bug [#1213]: ability to run tests for external standards using the PHPCS native test framework was broken.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#1215]: PHP 8.5 "Using null as an array offset" deprecation notices.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

[#1213]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1213
[#1215]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1215


## [3.13.3] - 2025-09-04

### Added
- Tokenizer support for PHP 8.4 dereferencing of new expressions without wrapping parentheses. [#1160]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Tokenizer support for PHP 8.4 `abstract` properties. [#1183]
    - The `File::getMemberProperties()` method now also supports `abstract` properties through a new `is_abstract` array index in the return value. [#1184]
    - Additionally, the following sniffs have been updated to support `abstract` properties:
        - Generic.PHP.LowerCaseConstant [#1185]
        - Generic.PHP.UpperCaseConstant [#1185]
        - PSR2.Classes.PropertyDeclaration [#1188]
        - Squiz.Commenting.VariableComment [#1186]
        - Squiz.WhiteSpace.MemberVarSpacing [#1187]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches
- Tokenizer support for the PHP 8.4 "exit as a function call" change. [#1201]
    - When `exit`/`die` is used as a fully qualified "function call", it will now be tokenized as `T_NS_SEPARATOR` + `T_EXIT`.
    - Additionally, the following sniff has been updated to handle fully qualified exit/die correctly:
        - Squiz.PHP.NonExecutableCode
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches

### Changed
- Tokenizer/PHP: fully qualified `true`/`false`/`null` will now be tokenized as `T_NS_SEPARATOR` + `T_TRUE`/`T_FALSE`/`T_NULL`. [#1201]
    - Previously, these were tokenized as `T_NS_SEPARATOR` + `T_STRING`.
    - Additionally, the following sniffs have been updated to handle fully qualified true/false/null correctly:
        - Generic.CodeAnalysis.UnconditionalIfStatement
        - Generic.ControlStructures.DisallowYodaConditions
        - PEAR.Functions.ValidDefaultValue
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.
- Generic.PHP.Syntax: the sniff is now able to scan input provided via STDIN on non-Windows OSes. [#915]
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- PSR2.ControlStructures.SwitchDeclaration: the `WrongOpener*` error code is now auto-fixable if the identified "wrong opener" is a semi-colon. [#1161]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The PSR2.Classes.PropertyDeclaration will now check that the abstract modifier keyword is placed before a visibility keyword. [#1188]
    - Errors will be reported via a new `AbstractAfterVisibility` error code.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Bernhard Zwein][@benno5020], [Rick Kerkhof][@NanoSector], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#1112] : `--parallel` option fails if PHP_CodeSniffer is invoked via bash and the invokation creates a non-PHPCS-managed process.
    - Thanks to [Rick Kerkhof][@NanoSector] for the patch.
- Fixed bug [#1113] : fatal error when the specified "files to scan" would result in the same file being added multiple times to the queue.
    - This error only occured when `--parallel` scanning was enabled.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#1154] : PEAR.WhiteSpace.ObjectOperatorIndent: false positive when checking multiple chained method calls in a multidimensional array.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#1193] : edge case inconsistency in how empty string array keys for sniff properties are handled.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#1197] : Squiz.Commenting.FunctionComment: return types containing a class name with underscores would be truncated leading to incorrect results.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

### Other
- The [Wiki documentation] is now publicly editable. :tada:
    - Update proposals can be submittted by opening a pull request in the [PHPCSStandards/PHP_CodeSniffer-documentation][docs-repo] repository.
        Contributions welcome !
    - Thanks to [Anna Filina][@afilina], [Dan Wallis][@fredden] and [Juliette Reinders Folmer][@jrfnl] for their work on getting this set up.
- The [Phar website] has had a facelift. [#107]
    - Thanks to [Bernhard Zwein][@benno5020] for making this happen!

[Wiki documentation]: https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki
[docs-repo]:          https://github.com/PHPCSStandards/PHP_CodeSniffer-documentation
[Phar website]:       https://phars.phpcodesniffer.com/

[#107]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/107
[#915]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/915
[#1112]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1112
[#1113]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1113
[#1154]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1154
[#1160]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1160
[#1161]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1161
[#1183]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1183
[#1184]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1184
[#1185]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1185
[#1186]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1186
[#1187]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1187
[#1188]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1188
[#1193]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1193
[#1197]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1197
[#1201]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1201


## [3.13.2] - 2025-06-18

### Changed
- The documentation for the following sniffs has been improved:
    - Squiz.Classes.SelfMemberReference
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#1135] : Squiz.Functions.FunctionDeclarationArgumentSpacing: typo in new error code `SpacingAfterSetVis\[i\]bility`.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

[#1135]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1135


## [3.13.1] - 2025-06-13

### Added
- Added support for PHP 8.4 properties with asymmetric visibility to File::getMemberProperties() through a new `set_scope` array index in the return value. [#1116]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.
- Added support for PHP 8.4 (constructor promoted) properties with asymmetric visibility to File::getMethodParameters() through new `set_visibility` and `set_visibility_token` array indexes in the return value. [#1116]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.
- Added support for PHP 8.4 asymmetric visibility modifiers to the following sniffs:
    - Generic.PHP.LowerCaseKeyword [#1117]
    - PEAR.NamingConventions.ValidVariableName [#1118]
    - PSR2.Classes.PropertyDeclaration [#1119]
    - Squiz.Commenting.BlockComment [#1120]
    - Squiz.Commenting.DocCommentAlignment [#1120]
    - Squiz.Commenting.VariableComment [#1120]
    - Squiz.Functions.FunctionDeclarationArgumentSpacing [#1121]
    - Squiz.Scope.MemberVarScope [#1122]
    - Squiz.WhiteSpace.MemberVarSpacing [#1123]
    - Squiz.WhiteSpace.ScopeKeywordSpacing [#1124]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.

### Changed
- The PSR2.Classes.PropertyDeclaration will now check that a set-visibility modifier keyword is placed after a potential general visibility keyword. [#1119]
    - Errors will be reported via a new `AvizKeywordOrder` error code.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The Squiz.Functions.FunctionDeclarationArgumentSpacing will now check spacing after a set-visibility modifier keyword. [#1121]
    - Errors will be reported via a new `SpacingAfterSetVisibility` error code.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The Squiz.Scope.MemberVarScope will now flag missing "read" visibility, when "write" visibility is set, under a separate error code `AsymReadMissing`. [#1122]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The documentation for the following sniffs has been improved:
    - PEAR.Classes.ClassDeclaration
    - Squiz.WhiteSpace.FunctionOpeningBraceSpace
    - Thanks to [Brian Dunne][@braindawg] and [Rodrigo Primo][@rodrigoprimo] for the patches.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Dan Wallis][@fredden], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Other
- The latest PHP_CodeSniffer XSD file is now available via the following permalink: <https://schema.phpcodesniffer.com/phpcs.xsd>. [#1094]
    Older XSD files can be referenced via permalinks based on their minor: `https://schema.phpcodesniffer.com/#.#/phpcs.xsd`.
- The GPG signature for the PHAR files has been rotated. The new fingerprint is: D91D86963AF3A29B6520462297B02DD8E5071466.

[#1094]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1094
[#1116]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1116
[#1117]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1117
[#1118]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1118
[#1119]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1119
[#1120]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1120
[#1121]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1121
[#1122]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1122
[#1123]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1123
[#1124]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1124


## [3.13.0] - 2025-05-11

### Added
- Added support for PHP 8.4 asymmetric visibility modifiers to the tokenizer. [#871]
    - Thanks to [Daniel Scherzer][@DanielEScherzer] for the patch.
- Added support for PHP 8.4 `final` properties to the following sniffs:
    - PSR2.Classes.PropertyDeclaration [#950]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.

### Changed
- Generic.WhiteSpace.LanguageConstructSpacing: will now also check the spacing after the `goto` language construct keyword. [#917]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The PSR2.Classes.PropertyDeclaration will now check that the `final` modifier keyword is placed before a visibility keyword. [#950]
    - Errors will be reported via a new `FinalAfterVisibility` error code.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Improved Help information about the `--reports` CLI flag. [#1078]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The documentation for the following sniffs has been improved:
    - PSR1.Files.SideEffects
    - PSR2.ControlStructures.SwitchDeclaration
    - PSR2.Namespaces.NamespaceDeclaration
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patches.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Deprecated

- Nearly everything which was soft deprecated before is now hard deprecated and will show deprecation notices:
    - This applies to:
        - All sniffs which will be removed in 4.0. [#888]
        - The deprecated Generator methods. [#889]
        - The old array property setting format (via comma separated strings). [#890]
        - Sniffs not implementing the `PHP_CodeSniffer\Sniffs\Sniff` interface. [#891]
        - Sniffs not following the naming conventions. [#892]
        - Standards called Internal. [#893]
        - Sniffs which don't listen for PHP, like JS/CSS specific sniffs. [#894]
    - The deprecation notices can be silenced by using the `-q` (=quiet) CLI flag.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.

### Fixed
- Fixed bug [#1040] : Generic.Strings.UnnecessaryHeredoc - false positive for heredocs containing escape sequences.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#1040] : Generic.Strings.UnnecessaryHeredoc - fixer would not clean up escape sequences which aren't necessary in nowdocs.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#1048] : A file under scan would sometimes be updated with partial fixes, even though the file "failed to fix".
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

### Other
**Calling all testers!**

The first beta release for PHP_CodeSniffer 4.0 has been tagged. Please help by testing the beta release and reporting any issues you run into.
Upgrade guides for both [ruleset maintainers/end-users][wiki-upgrade-guide-users-40], as well as for [sniff developers and integrators][wiki-upgrade-guide-devs-40], have been published to the Wiki to help smooth the transition.

[wiki-upgrade-guide-users-40]: https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Version-4.0-User-Upgrade-Guide
[wiki-upgrade-guide-devs-40]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Version-4.0-Developer-Upgrade-Guide

[#871]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/871
[#888]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/888
[#889]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/889
[#890]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/890
[#891]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/891
[#892]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/892
[#893]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/893
[#894]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/894
[#917]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/917
[#950]:  https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/950
[#1040]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1040
[#1048]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1048
[#1078]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/1078

## [3.12.2] - 2025-04-13

### Added
- Added support for PHP 8.4 `final` properties to the following sniffs:
    - Generic.PHP.LowerCaseConstant [#948]
    - Generic.PHP.UpperCaseConstant [#948]
    - Squiz.Commenting.DocCommentAlignment [#951]
    - Squiz.Commenting.VariableComment [#949]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.

### Changed
- Tokenizer/PHP: a PHP open tag at the very end of a file will now always be tokenized as T_OPEN_TAG, independently of the PHP version. [#937]
    - Previously, a PHP open tag at the end of a file was not tokenized as an open tag on PHP < 7.4 and the tokenization would depend on the `short_open_tag` setting.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- PEAR.Commenting.FunctionComment: improved message for "blank lines between docblock and declaration" check. [#830]
- The documentation for the following sniffs has been improved:
    - Generic.Functions.OpeningFunctionBraceBsdAllman
    - Generic.Functions.OpeningFunctionBraceKernighanRitchie
    - Generic.WhiteSpace.LanguageConstructSpacing
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patches.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#830] : PEAR.Commenting.FunctionComment will no longer remove blank lines within attributes.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#929] : Generic.PHP.ForbiddenFunctions: prevent false positives/negatives for code interlaced with comments.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#934] : Generic.PHP.LowerCaseConstant and Generic.PHP.UpperCaseConstant will now correctly ignore DNF types for properties.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#936] : Squiz.Commenting.FunctionCommentThrowTag: sniff would bow out when function has attributes attached, leading to false negatives.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#940] : Squiz.Commenting.VariableComment: false positive for missing docblock for properties using DNF types.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#944] : Squiz.Commenting.FunctionComment did not support DNF/intersection types in `@param` tags.
    - Thanks to [Jeffrey Angenent][@devfrey] for the patch.
- Fixed bug [#945] : Squiz.WhiteSpace.FunctionSpacing would get confused when there are two docblocks above a function declaration.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#947] : Squiz.Commenting.FunctionCommentThrowTag: prevent false positives/negatives for code interlaced with comments.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#951] : Squiz.Commenting.DocCommentAlignment did not examine docblocks for `final` classes.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#955] : Potential race condition, leading to a fatal error, when both the `Diff` + the `Code` reports are requested and caching is on.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#956] : Generic.WhiteSpace.ScopeIndent: undefined array index notice when running in debug mode.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

[#830]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/830
[#929]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/929
[#934]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/934
[#936]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/936
[#937]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/937
[#940]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/940
[#944]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/944
[#945]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/945
[#947]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/947
[#948]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/948
[#949]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/949
[#951]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/951
[#955]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/955
[#956]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/956

## [3.12.1] - 2025-04-04

### Added
- Documentation for the following sniffs:
    - Squiz.Commenting.BlockComment
    - Thanks to [Colin Stewart][@costdev] for the patch.

### Changed
- Generic.WhiteSpace.HereNowdocIdentifierSpacing: improved error message text.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Deprecated
- The `Generic.Functions.CallTimePassByReference` sniff. See [#921].
    - This sniff will be removed in version 4.0.0.

### Fixed
- Fixed bug [#906] : Fixer: prevent `InvalidArgumentException`s when displaying verbose information.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#907] : Tokenizer/PHP: tokenization of tokens related to union, intersection and DNF types in combination with PHP 8.4 final properties.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#908] : Tokenizer/PHP: tokenization of `?` in nullable types for readonly properties.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#916] : Tokenizer/PHP: `goto` was not recognized as a terminating statement for a case/default in a switch control structure.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

### Other
- PHP_CodeSniffer 4.0 is coming soon! Interested in a sneak peek ? Join the live stream at any time on April 14, 15, 17 or 18.
    Read the open invitation ([#924]) for all the details.

[#906]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/906
[#907]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/907
[#908]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/908
[#916]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/916
[#921]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/921
[#924]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/924

## [3.12.0] - 2025-03-18

### Added
- Added support for PHP 8.4 `final` properties to File::getMemberProperties() through a new `is_final` array index in the return value. [#834]
    - Thanks to [Daniel Scherzer][@DanielEScherzer] for the patch.
- Generators/HTML: each section title now has a unique anchor link, which can be copied when hovering over a title. [#859]
    - This should make sharing a link to a specific section of the documentation more straight-forward.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Documentation for the following sniffs:
    - Squiz.Classes.ClassFileName
    - Squiz.Classes.ValidClassName
    - Thanks to [Brian Dunne][@braindawg] for the patches.

### Changed
- PHPCBF: the messaging when no fixable errors are found will now distinguish between "No violations" (at all) versus "No fixable errors". [#806]
    - Thanks to [Peter Wilson][@peterwilsoncc] for the patch.
- The `-h` (Help) option now contains a more extensive list of "config" options which can be set. [#809]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Improved error message when invalid sniff codes are supplied to `--sniffs` or `--exclude` command line arguments. [#344]
    - Thanks to [Dan Wallis][@fredden] for the patch.
- Improved error message when an invalid generator name is supplied to the `--generator` command line argument. [#709], [#771]
    - The generator name will now also always be handled case-insensitively, independently of the OS used.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- The user will be shown an informative error message for sniffs missing one of the required methods. [#873]
    - Previously this would result in a fatal error.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Ruleset processing will now be allowed to run to its conclusion - barring critical errors - before displaying all ruleset errors in one go. [#857]
    - Previously an error in a ruleset would cause PHPCS to exit immediately and show only one error at a time.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators: XML documentation files which don't contain any actual documentation will now silently be ignored. [#755]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators: when the `title` attribute is missing, the documentation generation will now fall back to the sniff name as the title. [#820]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators: cleaner output based on the elements of the documentation which are available. [#819], [#821]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators/HTML: improved display of code tables by using semantic HTML. [#854]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Squiz.Classes.ClassFileName: recommend changing the file name instead of changing the class name. [#845]
    - This prevents unactionable recommendations due to the file name not translating to a valid PHP symbol name.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Squiz.Functions.FunctionDeclarationArgumentSpacing: incorrect spacing after a comma followed by a promoted property has an improved error message and will now be flagged with the `SpacingBeforePropertyModifier` or `NoSpaceBeforePropertyModifier` error codes. [#792]
    - This was previously already flagged, but using either the `SpacingBeforeHint` or `NoSpaceBeforeHint` error code, which was misleading.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Squiz.Functions.FunctionDeclarationArgumentSpacing: the sniff will now also check the spacing after property modifiers for promoted properties in constructor methods. [#792]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Squiz.WhiteSpace.ScopeKeywordSpacing: the sniff will now also check the spacing after the `final` and `abstract` modifier keywords. [#604]
    - Thanks to [Klaus Purer][@klausi] for the patch.
- The following sniff(s) have received efficiency improvements:
    - Squiz.WhiteSpace.ScopeKeywordSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.
- Incorrectly set inline properties (in test case files) will be silently ignored again. [#884]
    - This removes the `Internal.PropertyDoesNotExist` error code.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The AbstractMethodUnitTest class will now flag duplicate test case markers in a test case file. [#773]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Asis Pattisahusiwa][@asispts], [Dan Wallis][@fredden], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Deprecated
All deprecation are slated for removal in PHP_CodeSniffer 4.0.

- Support for sniffs not implementing the PHPCS `Sniff` interface. See [#694].
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Support for including sniffs which don't comply with the PHPCS naming conventions (by referencing the sniff file directly). See [#689].
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Support for external standards named "Internal". See [#799].
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The following Generator methods are now (soft) deprecated. See [#755]:
    - `PHP_CodeSniffer\Generators\Text::printTitle()` in favour of `PHP_CodeSniffer\Generators\Text::getFormattedTitle()`
    - `PHP_CodeSniffer\Generators\Text::printTextBlock()` in favour of `PHP_CodeSniffer\Generators\Text::getFormattedTextBlock()`
    - `PHP_CodeSniffer\Generators\Text::printCodeComparisonBlock()` in favour of `PHP_CodeSniffer\Generators\Text::getFormattedCodeComparisonBlock()`
    - `PHP_CodeSniffer\Generators\Markdown::printHeader()` in favour of `PHP_CodeSniffer\Generators\Markdown::getFormattedHeader()`
    - `PHP_CodeSniffer\Generators\Markdown::printFooter()` in favour of `PHP_CodeSniffer\Generators\Markdown::getFormattedFooter()`
    - `PHP_CodeSniffer\Generators\Markdown::printTextBlock()` in favour of `PHP_CodeSniffer\Generators\Markdown::getFormattedTextBlock()`
    - `PHP_CodeSniffer\Generators\Markdown::printCodeComparisonBlock()` in favour of `PHP_CodeSniffer\Generators\Markdown::getFormattedCodeComparisonBlock()`
    - `PHP_CodeSniffer\Generators\HTML::printHeader()` in favour of `PHP_CodeSniffer\Generators\HTML::getFormattedHeader()`
    - `PHP_CodeSniffer\Generators\HTML::printToc()` in favour of `PHP_CodeSniffer\Generators\HTML::getFormattedToc()`
    - `PHP_CodeSniffer\Generators\HTML::printFooter()` in favour of `PHP_CodeSniffer\Generators\HTML::getFormattedFooter()`
    - `PHP_CodeSniffer\Generators\HTML::printTextBlock()` in favour of `PHP_CodeSniffer\Generators\HTML::getFormattedTextBlock()`
    - `PHP_CodeSniffer\Generators\HTML::printCodeComparisonBlock()` in favour of `PHP_CodeSniffer\Generators\HTML::getFormattedCodeComparisonBlock()`
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

### Fixed
- Fixed bug [#794] : Generators: prevent fatal error when the XML documentation does not comply with the expected format.
    - It is recommended to validate XML documentation files against the XSD file: <https://phpcsstandards.github.io/PHPCSDevTools/phpcsdocs.xsd>
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#814] : Generic.NamingConventions.ConstructorName: prevent potential fatal errors during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#816] : File::getDeclarationName(): prevent incorrect result for unfinished closures during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#817] : Squiz.Classes.ValidClassName: ignore comments when determining the name to be validated.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#825] : Squiz.Classes.ClassDeclaration: false positives when the next thing after a class was a function with an attribute attached.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#826] : Squiz.WhiteSpace.FunctionSpacing: prevent incorrect some results when attributes are attached to a function.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#827] : PEAR.Functions.FunctionDeclaration: fixer conflict over an unfinished closure during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#828] : Squiz.WhiteSpace.MemberVarSpacing: allow for `readonly` properties.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#832] : Squiz.WhiteSpace.MemberVarSpacing: prevent potential fixer conflict during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#833] : Squiz.PHP.EmbeddedPhp: fixer conflict when a PHP open tag for a multi-line snippet is found on the same line as a single-line embedded PHP snippet.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#833] : Squiz.PHP.EmbeddedPhp: incorrect indent calculation in certain specific situations.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#835] : Generic.PHP.DisallowShortOpenTag: don't act on parse errors.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#838] : Squiz.PHP.EmbeddedPhp: no new line before close tag was incorrectly enforced when a preceding OO construct or function had a trailing comment after the close curly.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#840] : Squiz.WhiteSpace.MemberVarSpacing: more accurate reporting on blank lines in the property "pre-amble" (i.e. docblock, attributes).
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#845] : Squiz.Classes.ClassFileName: don't throw an incorrect error for an unfinished OO declaration during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#865] : Setting an array property to an empty array from an XML ruleset now works correctly.
    - Previously, the property value would be set to `[0 => '']`.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#866] : Squiz.WhiteSpace.FunctionOpeningBraceSpace: XML docs were not accessible due to an issue with the file name.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

### Other
- A new [wiki page][wiki-about-standards] is available to clarify the difference between a project ruleset and an external standard.
    - This wiki page also contains detailed information about the naming conventions external standards must comply with.
- A new [XMLLint validate][xmllint-validate] action runner is available which can be used in CI to validate rulesets for PHP_CodeSniffer against the XSD.

[wiki-about-standards]: https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/About-Standards-for-PHP_CodeSniffer
[xmllint-validate]: https://github.com/marketplace/actions/xmllint-validate

[#344]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/344
[#604]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/604
[#689]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/689
[#694]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/694
[#709]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/709
[#755]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/755
[#771]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/771
[#773]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/773
[#792]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/792
[#794]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/794
[#799]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/799
[#806]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/806
[#809]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/809
[#814]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/814
[#816]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/816
[#817]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/817
[#819]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/819
[#820]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/820
[#821]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/821
[#825]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/825
[#826]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/826
[#827]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/827
[#828]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/828
[#832]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/832
[#833]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/833
[#834]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/834
[#835]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/835
[#838]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/838
[#840]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/840
[#845]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/845
[#854]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/854
[#857]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/857
[#859]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/859
[#865]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/865
[#866]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/866
[#873]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/873
[#884]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/884

## [3.11.3] - 2025-01-23

### Changed
- Generic.ControlStructures.InlineControlStructure no longer unnecessarily listens for T_SWITCH tokens. [#595]
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Squiz.Functions.FunctionDeclarationArgumentSpacing: improvements to error message for `SpaceBeforeComma` error. [#783]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The following sniff(s) have received efficiency improvements:
    - Squiz.Functions.FunctionDeclarationArgumentSpacing
    - Thanks to [Dan Wallis][@fredden] and [Juliette Reinders Folmer][@jrfnl] for the patches.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Michał Bundyra][@michalbundyra], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#620] : Squiz.Functions.FunctionDeclarationArgumentSpacing: newlines after type will now be handled by the fixer. This also prevents a potential fixer conflict.
    - Thanks to [Dan Wallis][@fredden] for the patch.
- Fixed bug [#782] : Tokenizer/PHP: prevent an "Undefined array key" notice during live coding for unfinished arrow functions.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#783] : Squiz.Functions.FunctionDeclarationArgumentSpacing: new line after reference token was not flagged nor fixed.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#783] : Squiz.Functions.FunctionDeclarationArgumentSpacing: new line after variadic token was not flagged nor fixed.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#783] : Squiz.Functions.FunctionDeclarationArgumentSpacing: new line before/after the equal sign for default values was not flagged nor fixed when `equalsSpacing` was set to `0`.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#783] : Squiz.Functions.FunctionDeclarationArgumentSpacing: fixer conflict when a new line is found before/after the equal sign for default values and `equalsSpacing` was set to `1`.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#783] : Squiz.Functions.FunctionDeclarationArgumentSpacing: fixer for spacing before/after equal sign could inadvertently remove comment.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#783] : Squiz.Functions.FunctionDeclarationArgumentSpacing: fixer will now handle comments between the end of a parameter and a comma more cleanly.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#784] : Squiz.WhiteSpace.FunctionSpacing: prevent fixer conflict when a multi-line docblock would start on the same line as the function close curly being examined.
    - Thanks to [Klaus Purer][@klausi] for the patch

[#595]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/595
[#620]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/620
[#782]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/782
[#783]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/783
[#784]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/784

## [3.11.2] - 2024-12-11

### Changed
- Generators/HTML + Markdown: the output will now be empty (no page header/footer) when there are no docs to display. [#687]
    - This is in line with the Text Generator which already didn't produce output if there are no docs.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators/HTML: only display a Table of Contents when there is more than one sniff with documentation. [#697]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators/HTML: improved handling of line breaks in `<standard>` blocks. [#723]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators/Markdown: improved compatibility with the variety of available markdown parsers. [#722]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generators/Markdown: improved handling of line breaks in `<standard>` blocks. [#737]
    - This prevents additional paragraphs from being displayed as code blocks.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Generic.NamingConventions.UpperCaseConstantName: the exact token containing the non-uppercase constant name will now be identified with more accuracy. [#665]
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Generic.Functions.OpeningFunctionBraceKernighanRitchie: minor improvement to the error message wording. [#736]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#527] : Squiz.Arrays.ArrayDeclaration: short lists within a foreach condition should be ignored.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#665] : Generic.NamingConventions.UpperCaseConstantName: false positives and false negatives when code uses unconventional spacing and comments when calling `define()`.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#665] : Generic.NamingConventions.UpperCaseConstantName: false positive when a constant named `DEFINE` is encountered.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#665] : Generic.NamingConventions.UpperCaseConstantName: false positive for attribute class called `define`.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#665] : Generic.NamingConventions.UpperCaseConstantName: false positive when handling the instantiation of a class named `define`.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#688] : Generators/Markdown could leave error_reporting in an incorrect state.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#698] : Generators/Markdown : link in the documentation footer would not parse as a link.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#738] : Generators/Text: stray blank lines after code sample titles.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#739] : Generators/HTML + Markdown: multi-space whitespace within a code sample title was folded into a single space.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

[#527]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/527
[#665]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/665
[#687]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/687
[#688]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/688
[#697]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/697
[#698]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/698
[#722]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/722
[#723]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/723
[#736]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/736
[#737]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/737
[#738]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/738
[#739]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/739

## [3.11.1] - 2024-11-16

### Changed
- Output from the `--generator=...` feature will respect the OS-expected EOL char in more places. [#671]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Bartosz Dziewoński][@MatmaRex] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#674] : Generic.WhiteSpace.HereNowdocIdentifierSpacing broken XML documentation
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Fixed bug [#675] : InvalidArgumentException when a ruleset includes a sniff by file name and the included sniff does not comply with the PHPCS naming conventions.
    - Notwithstanding this fix, it is strongly recommended to ensure custom sniff classes comply with the PHPCS naming conventions.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

[#671]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/671
[#674]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/674
[#675]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/675

## [3.11.0] - 2024-11-12

### Added
- Runtime support for PHP 8.4. All known PHP 8.4 deprecation notices have been fixed.
    - Syntax support for new PHP 8.4 features will follow in a future release.
    - If you find any PHP 8.4 deprecation notices which were missed, please report them.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.
- Tokenizer support for PHP 8.3 "yield from" expressions with a comment between the keywords. [#529], [#647]
    - Sniffs explicitly handling T_YIELD_FROM tokens may need updating. The PR description contains example code for use by sniff developers.
    - Additionally, the following sniff has been updated to support "yield from" expressions with comments:
        - Generic.WhiteSpace.LanguageConstructSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- New `Generic.WhiteSpace.HereNowdocIdentifierSpacing` sniff. [#586], [#637]
    - Forbid whitespace between the `<<<` and the identifier string in heredoc/nowdoc start tokens.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- New `Generic.Strings.UnnecessaryHeredoc` sniff. [#633]
    - Warns about heredocs without interpolation or expressions in the body text and can auto-fix these to nowdocs.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Documentation for the following sniffs:
    - Generic.Arrays.ArrayIndent
    - Squiz.PHP.Heredoc
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for the patches.

### Changed
- The Common::getSniffCode() method will now throw an InvalidArgumentException exception if an invalid `$sniffClass` is passed. [#524], [#625]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Documentation generated using the `--generator=...` feature will now always be presented in natural order based on the sniff name(s). [#668]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Minor improvements to the display of runtime information. [#658]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Squiz.Commenting.PostStatementComment: trailing annotations in PHP files will now be reported under a separate, non-auto-fixable error code `AnnotationFound`. [#560], [#627]
    - This prevents (tooling related) annotations from taking on a different meaning when moved by the fixer.
    - The separate error code also allows for selectively excluding it to prevent the sniff from triggering on trailing annotations, while still forbidding other trailing comments.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Squiz.ControlStructures.ForEachLoopDeclaration: the `SpacingAfterOpen` error code has been replaced by the `SpaceAfterOpen` error code. The latter is a pre-existing code. The former appears to have been a typo. [#582]
    - Thanks to [Dan Wallis][@fredden] for the patch.
- The following sniff(s) have received efficiency improvements:
    - Generic.Classes.DuplicateClassName
    - Generic.NamingConventions.ConstructorName
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for the patches.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#3808][sq-3808] : Generic.WhiteSpace.ScopeIndent would throw false positive for tab indented multi-token yield from expression.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#630] : The tokenizer could inadvertently transform "normal" parentheses to DNF parentheses, when a function call was preceded by a switch-case / alternative syntax control structure colon.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#645] : On PHP 5.4, if yield was used as the declaration name for a function declared to return by reference, the function name would incorrectly be tokenized as T_YIELD instead of T_STRING.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#647] : Tokenizer not applying tab replacement in single token "yield from" keywords.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#647] : Generic.WhiteSpace.DisallowSpaceIndent did not flag space indentation in multi-line yield from.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#647] : Generic.WhiteSpace.DisallowTabIndent did not flag tabs inside yield from.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#652] : Generic.NamingConventions.ConstructorName: false positives for PHP-4 style calls to PHP-4 style parent constructor when a method with the same name as the parent class was called on another class.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#652] : Generic.NamingConventions.ConstructorName: false negatives for PHP-4 style calls to parent constructor for function calls with whitespace and comments in unconventional places.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#653] : Generic.Classes.DuplicateClassName : the sniff did not skip namespace keywords used as operators, which could lead to false positives.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#653] : Generic.Classes.DuplicateClassName : sniff going into an infinite loop during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#653] : Generic.Classes.DuplicateClassName : false positives/negatives when a namespace declaration contained whitespace or comments in unconventional places.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#653] : Generic.Classes.DuplicateClassName : namespace for a file going in/out of PHP was not remembered/applied correctly.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-3808]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3808
[#524]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/524
[#529]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/529
[#560]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/560
[#582]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/582
[#586]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/586
[#625]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/625
[#627]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/627
[#630]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/630
[#633]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/633
[#637]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/637
[#645]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/645
[#647]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/647
[#652]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/652
[#653]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/653
[#658]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/658
[#668]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/668

## [3.10.3] - 2024-09-18

### Changed
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#553] : Squiz.Classes.SelfMemberReference: false negative(s) when namespace operator was encountered between the namespace declaration and the OO declaration.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#579] : AbstractPatternSniff: potential PHP notice during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#580] : Squiz.Formatting.OperatorBracket: potential PHP notice during live coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#581] : PSR12.ControlStructures.ControlStructureSpacing: prevent fixer conflict by correctly handling multiple empty newlines before the first condition in a multi-line control structure.
    - Thanks to [Dan Wallis][@fredden] for the patch.
- Fixed bug [#585] : Tokenizer not applying tab replacement in heredoc/nowdoc openers.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#588] : Squiz.PHP.EmbeddedPhp false positive when checking spaces after a PHP short open tag.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#597] : Generic.PHP.LowerCaseKeyword did not flag nor fix non-lowercase anonymous class keywords.
    - Thanks to [Marek Štípek][@maryo] for the patch.
- Fixed bug [#598] : Squiz.PHP.DisallowMultipleAssignments: false positive on assignments to variable property on object stored in array.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#608] : Squiz.Functions.MultiLineFunctionDeclaration did not take (parameter) attributes into account when checking for one parameter per line.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Other
- The provenance of PHAR files associated with a release can now be verified via [GitHub Artifact Attestations][ghattest] using the [GitHub CLI tool][ghcli] with the following command: `gh attestation verify [phpcs|phpcbf].phar -o PHPCSStandards`. [#574]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.

[#553]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/553
[#574]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/574
[#579]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/579
[#580]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/580
[#581]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/581
[#585]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/585
[#588]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/588
[#597]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/597
[#598]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/598
[#608]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/608

[ghcli]:    https://cli.github.com/
[ghattest]: https://docs.github.com/en/actions/how-tos/secure-your-work/use-artifact-attestations/use-artifact-attestations

## [3.10.2] - 2024-07-22

### Changed
- The following sniff(s) have received efficiency improvements:
    - Generic.Functions.FunctionCallArgumentSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The array format of the information passed to the `Reports::generateFileReport()` method is now documented in the Reports interface. [#523]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Bill Ruddock][@biinari], [Dan Wallis][@fredden], [Klaus Purer][@klausi], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#513] : Generic.Functions.FunctionCallArgumentSpacing did not ignore the body of a match expressions passed as a function argument, which could lead to false positives.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#533] : Generic.WhiteSpace.DisallowTabIndent: tab indentation for heredoc/nowdoc closers will no longer be auto-fixed to prevent parse errors. The issue will still be reported.
    - The error code for heredoc/nowdoc indentation using tabs has been made more specific - `TabsUsedHeredocCloser` - to allow for selectively excluding the indentation check for heredoc/nowdoc closers.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#534] : Generic.WhiteSpace.DisallowSpaceIndent did not report on space indentation for PHP 7.3 flexible heredoc/nowdoc closers.
    - Closers using space indentation will be reported with a dedicated error code: `SpacesUsedHeredocCloser`.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#537] : Squiz.PHP.DisallowMultipleAssignments false positive for list assignments at the start of a new PHP block after an embedded PHP statement.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#551] : Squiz.PHP.DisallowMultipleAssignments prevent false positive for function parameters during live coding.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#554] : Generic.CodeAnalysis.UselessOverridingMethod edge case false negative when the call to the parent method would end on a PHP close tag.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#555] : Squiz.Classes.SelfMemberReference edge case false negative when the namespace declaration would end on a PHP close tag.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[#513]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/513
[#523]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/523
[#533]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/533
[#534]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/534
[#537]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/537
[#551]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/551
[#554]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/554
[#555]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/555

## [3.10.1] - 2024-05-22

### Added
- Documentation for the following sniffs:
    - Generic.Commenting.DocComment
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.

### Changed
- The following have received efficiency improvements:
    - Type handling in the PHP Tokenizer
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#110], [#437], [#475] : `File::findStartOfStatement()`: the start of statement/expression determination for tokens in parentheses/short array brackets/others scopes, nested within match expressions, was incorrect in most cases.
    The trickle down effect of the bug fixes made to the `File::findStartOfStatement()` method, is that the Generic.WhiteSpace.ScopeIndent and the PEAR.WhiteSpace.ScopeIndent sniffs should now be able to correctly determine and fix the indent for match expressions containing nested expressions.
    These fixes also fix an issue with the `Squiz.Arrays.ArrayDeclaration` sniff and possibly other, unreported bugs.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#504] : The tokenizer could inadvertently mistake the last parameter in a function call using named arguments for a DNF type.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#508] : Tokenizer/PHP: extra hardening against handling parse errors in the type handling layer.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[#110]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/110
[#437]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/437
[#475]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/475
[#504]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/504
[#508]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/508

## [3.10.0] - 2024-05-20

### Added
- Tokenizer support for PHP 8.2 Disjunctive Normal Form (DNF) types. [#3731][sq-3731], [#387], [#461]
    - Includes new `T_TYPE_OPEN_PARENTHESIS` and `T_TYPE_CLOSE_PARENTHESIS` tokens to represent the parentheses in DNF types.
    - These new tokens, like other parentheses, will have the `parenthesis_opener` and `parenthesis_closer` token array indexes set and the tokens between them will have the `nested_parenthesis` index.
    - The `File::getMethodProperties()`, `File::getMethodParameters()` and `File::getMemberProperties()` methods now all support DNF types. [#471], [#472], [#473]
    - Additionally, the following sniff has been updated to support DNF types:
        - Generic.PHP.LowerCaseType [#478]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches.
- Documentation for the following sniffs:
    - Squiz.WhiteSpace.FunctionClosingBraceSpace
    - Thanks to [Przemek Hernik][@przemekhernik] for the patch.

### Changed
- The help screens have received a face-lift for improved usability and readability. [#447]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch and thanks to [Colin Stewart][@costdev], [Gary Jones][@GaryJones] and [@mbomb007] for reviewing.
- The Squiz.Commenting.ClosingDeclarationComment sniff will now also examine and flag closing comments for traits. [#442]
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- The following sniff(s) have efficiency improvements:
    - Generic.Arrays.ArrayIndent
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- The autoloader will now always return a boolean value indicating whether it has loaded a class or not. [#479]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Dan Wallis][@fredden], [Danny van der Sluijs][@DannyvdSluijs], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#466] : Generic.Functions.CallTimePassByReference was not flagging call-time pass-by-reference in class instantiations using the self/parent/static keywords.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#494] : edge case bug in tokenization of an empty block comment.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#494] : edge case bug in tokenization of an empty single-line DocBlock.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#499] : Generic.ControlStructures.InlineControlStructure now handles statements with a comment between `else` and `if` correctly.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.

[sq-3731]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3731
[#387]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/387
[#442]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/442
[#447]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/447
[#461]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/461
[#466]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/466
[#471]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/471
[#472]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/472
[#473]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/473
[#478]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/478
[#479]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/479
[#494]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/494
[#499]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/499

## [3.9.2] - 2024-04-24

### Changed
- The Generic.ControlStructures.DisallowYodaConditions sniff no longer listens for the null coalesce operator. [#458]
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Dan Wallis][@fredden], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#381] : Squiz.Commenting.ClosingDeclarationComment could throw the wrong error when the close brace being examined is at the very end of a file.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#385] : Generic.CodeAnalysis.JumbledIncrementer improved handling of parse errors/live coding.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#394] : Generic.Functions.CallTimePassByReference was not flagging call-time pass-by-reference in anonymous class instantiations
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#420] : PEAR.Functions.FunctionDeclaration could run into a blocking PHP notice while fixing code containing a parse error.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#421] : File::getMethodProperties() small performance improvement & more defensive coding.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#423] : PEAR.WhiteSpace.ScopeClosingBrace would have a fixer conflict with itself when a close tag was preceded by non-empty inline HTML.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#424] : PSR2.Classes.ClassDeclaration using namespace relative interface names in the extends/implements part of a class declaration would lead to a fixer conflict.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#427] : Squiz.Operators.OperatorSpacing would have a fixer conflict with itself when an operator was preceeded by a new line and the previous line ended in a comment.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#430] : Squiz.ControlStructures.ForLoopDeclaration: fixed potential undefined array index notice
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#431] : PSR2.Classes.ClassDeclaration will no longer try to auto-fix multi-line interface implements statements if these are interlaced with comments on their own line. This prevents a potential fixer conflict.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#453] : Arrow function tokenization was broken when the return type was a stand-alone `true` or `false`; or contained `true` or `false` as part of a union type.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Other
- [ESLint 9.0] has been released and changes the supported configuration file format.
    The (deprecated) `Generic.Debug.ESLint` sniff only supports the "old" configuration file formats and when using the sniff to run ESLint, the `ESLINT_USE_FLAT_CONFIG=false` environment variable will need to be set when using ESLint >= 9.0.
    For more information, see [#436].


[ESLint 9.0]: https://eslint.org/blog/2024/04/eslint-v9.0.0-released/#flat-config-is-now-the-default-and-has-some-changes

[#381]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/381
[#385]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/385
[#394]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/394
[#420]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/420
[#421]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/421
[#423]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/423
[#424]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/424
[#427]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/427
[#430]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/430
[#431]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/431
[#436]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/436
[#453]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/453
[#458]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/458

## [3.9.1] - 2024-03-31

### Added
- Documentation for the following sniffs:
    - Generic.PHP.RequireStrictTypes
    - Squiz.WhiteSpace.MemberVarSpacing
    - Squiz.WhiteSpace.ScopeClosingBrace
    - Squiz.WhiteSpace.SuperfluousWhitespace
    - Thanks to [Jay McPartland][@jaymcp] and [Rodrigo Primo][@rodrigoprimo] for the patches.

### Changed
- The following sniffs have received performance related improvements:
    - Generic.CodeAnalysis.UselessOverridingMethod
    - Generic.Files.ByteOrderMark
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patches.
- Performance improvement for the "Diff" report. Should be most notable for Windows users. [#355]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- The test suite has received some performance improvements. Should be most notable contributors using Windows. [#351]
    - External standards with sniff tests using the PHP_CodeSniffer native test framework will also benefit from these changes.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch.
- Various housekeeping, including improvements to the tests and documentation.
    - Thanks to [Jay McPartland][@jaymcp], [João Pedro Oliveira][@jpoliveira08], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions.

### Fixed
- Fixed bug [#289] : Squiz.WhiteSpace.OperatorSpacing and PSR12.Operators.OperatorSpacing : improved fixer conflict protection by more strenuously avoiding handling operators in declare statements.
    - Thanks to [Dan Wallis][@fredden] for the patch.
- Fixed bug [#366] : Generic.CodeAnalysis.UselessOverridingMethod : prevent false negative when the declared method name and the called method name do not use the same case.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch.
- Fixed bug [#368] : Squiz.Arrays.ArrayDeclaration fixer did not handle static closures correctly when moving array items to their own line.
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch.
- Fixed bug [#404] : Test framework : fixed PHP 8.4 deprecation notice.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[#289]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/289
[#351]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/351
[#355]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/355
[#366]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/366
[#368]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/368
[#404]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/404

## [3.9.0] - 2024-02-16

### Added
- Tokenizer support for PHP 8.3 typed class constants. [#321]
    - Additionally, the following sniffs have been updated to support typed class constants:
        - Generic.NamingConventions.UpperCaseConstantName [#332]
        - Generic.PHP.LowerCaseConstant [#330]
        - Generic.PHP.LowerCaseType [#331]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches
- Tokenizer support for PHP 8.3 readonly anonymous classes. [#309]
    - Additionally, the following sniffs have been updated to support readonly anonymous classes:
        - PSR12.Classes.ClassInstantiation [#324]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches
- New `PHP_CodeSniffer\Sniffs\DeprecatedSniff` interface to allow for marking a sniff as deprecated. [#281]
    - If a ruleset uses deprecated sniffs, deprecation notices will be shown to the end-user before the scan starts.
        When running in `-q` (quiet) mode, the deprecation notices will be hidden.
    - Deprecated sniffs will still run and using them will have no impact on the exit code for a scan.
    - In ruleset "explain"-mode (`-e`) an asterix `*` will show next to deprecated sniffs.
    - Sniff maintainers are advised to read through the PR description for full details on how to use this feature for their own (deprecated) sniffs.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- New `Generic.CodeAnalysis.RequireExplicitBooleanOperatorPrecedence` sniff. [#197]
    - Forbid mixing different binary boolean operators within a single expression without making precedence clear using parentheses
    - Thanks to [Tim Düsterhus][@TimWolla] for the contribution
- Squiz.PHP.EmbeddedPhp : the sniff will now also examine the formatting of embedded PHP statements using short open echo tags. [#27]
    - Includes a new `ShortOpenEchoNoSemicolon` errorcode to allow for selectively ignoring missing semicolons in single line embedded PHP snippets within short open echo tags.
    - The other error codes are the same and do not distinguish between what type of open tag was used.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Documentation for the following sniffs:
    - Generic.WhiteSpace.IncrementDecrementSpacing
    - PSR12.ControlStructures.ControlStructureSpacing
    - PSR12.Files.ImportStatement
    - PSR12.Functions.ReturnTypeDeclaration
    - PSR12.Properties.ConstantVisibility
    - Thanks to [Denis Žoljom][@dingo-d] and [Rodrigo Primo][@rodrigoprimo] for the patches

### Changed
- The Performance report can now also be used for a `phpcbf` run. [#308]
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Sniff tests which extend the PHPCS native `AbstractSniffUnitTest` class will now show a (non-build-breaking) warning when test case files contain fixable errors/warnings, but there is no corresponding `.fixed` file available in the test suite to verify the fixes against. [#336]
    - The warning is only displayed on PHPUnit 7.3.0 and higher.
    - The warning will be elevated to a test failure in PHPCS 4.0.
    - Thanks to [Dan Wallis][@fredden] for the patch
- The following sniffs have received performance related improvements:
    - Squiz.PHP.EmbeddedPhp
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Various housekeeping, including improvements to the tests and documentation
    - Thanks to [Dan Wallis][@fredden], [Joachim Noreiko][@joachim-n], [Remi Collet][@remicollet], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions

### Deprecated
- Support for scanning JavaScript and CSS files. See [#2448][sq-2448].
    - This also means that all sniffs which are only aimed at JavaScript or CSS files are now deprecated.
    - The Javascript and CSS Tokenizers, all Javascript and CSS specific sniffs, and support for JS and CSS in select sniffs which support multiple file types, will be removed in version 4.0.0.
- The abstract `PHP_CodeSniffer\Filters\ExactMatch::getBlacklist()` and `PHP_CodeSniffer\Filters\ExactMatch::getWhitelist()` methods are deprecated and will be removed in the 4.0 release. See [#198].
    - In version 4.0, these methods will be replaced with abstract `ExactMatch::getDisallowedFiles()` and `ExactMatch::getAllowedFiles()` methods
    - To make Filters extending `ExactMatch` cross-version compatible with both PHP_CodeSniffer 3.9.0+ as well as 4.0+, implement the new `getDisallowedFiles()` and `getAllowedFiles()` methods.
        - When both the `getDisallowedFiles()` and `getAllowedFiles()` methods as well as the `getBlacklist()` and `getWhitelist()` are available, the new methods will take precedence over the old methods.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The MySource standard and all sniffs in it. See [#2471][sq-2471].
    - The MySource standard and all sniffs in it will be removed in version 4.0.0.
- The `Zend.Debug.CodeAnalyzer` sniff. See [#277].
    - This sniff will be removed in version 4.0.0.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed bug [#127] : Squiz.Commenting.FunctionComment : The `MissingParamType` error code will now be used instead of `MissingParamName` when a parameter name is provided, but not its type. Additionally, invalid type hint suggestions will no longer be provided in these cases.
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#196] : Squiz.PHP.EmbeddedPhp : fixer will no longer leave behind trailing whitespace when moving code to another line.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#196] : Squiz.PHP.EmbeddedPhp : will now determine the needed indent with higher precision in multiple situations.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#196] : Squiz.PHP.EmbeddedPhp : fixer will no longer insert a stray new line when the closer of a multi-line embedded PHP block and the opener of the next multi-line embedded PHP block would be on the same line.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#235] : Generic.CodeAnalysis.ForLoopWithTestFunctionCall : prevent a potential PHP 8.3 deprecation notice during live coding
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch
- Fixed bug [#288] : Generic.WhiteSpace.IncrementDecrementSpacing : error message for post-in/decrement will now correctly inform about new lines found before the operator.
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch
- Fixed bug [#296] : Generic.WhiteSpace.ArbitraryParenthesesSpacing : false positive for non-arbitrary parentheses when these follow the scope closer of a `switch` `case`.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#307] : PSR2.Classes.ClassDeclaration : space between a modifier keyword and the `class` keyword was not checked when the space included a new line or comment.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#325] : Squiz.Operators.IncrementDecrementUsage : the sniff was underreporting when there was (no) whitespace and/or comments in unexpected places.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#335] : PSR12.Files.DeclareStatement : bow out in a certain parse error situation to prevent incorrect auto-fixes from being made.
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#340] : Squiz.Commenting.ClosingDeclarationComment : no longer adds a stray newline when adding a missing comment.
    - Thanks to [Dan Wallis][@fredden] for the patch

### Other
- A "Community cc list" has been introduced to ping maintainers of external standards and integrators for input regarding change proposals for PHP_CodeSniffer which may impact them. [#227]
    - For anyone who missed the discussion about this and is interested to be on this list, please feel invited to submit a PR to add yourself.
        The list is located in the `.github` folder.

[sq-2448]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2448
[sq-2471]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2471
[#27]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/27
[#127]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/127
[#196]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/196
[#197]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/197
[#198]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/198
[#227]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/227
[#235]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/235
[#277]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/277
[#281]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/281
[#288]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/288
[#296]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/296
[#307]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/307
[#308]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/308
[#309]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/309
[#321]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/321
[#324]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/324
[#325]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/325
[#330]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/330
[#331]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/331
[#332]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/332
[#335]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/335
[#336]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/336
[#340]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/340

## [3.8.1] - 2024-01-11

### Added
- Documentation has been added for the following sniffs:
    - Generic.CodeAnalysis.EmptyPHPStatement
    - Generic.Formatting.SpaceBeforeCast
    - Generic.PHP.Syntax
    - Generic.WhiteSpace.LanguageConstructSpacing
    - PSR12.Classes.ClosingBrace
    - PSR12.Classes.OpeningBraceSpace
    - PSR12.ControlStructures.BooleanOperatorPlacement
    - PSR12.Files.OpenTag
    - Thanks to [Rodrigo Primo][@rodrigoprimo] and [Denis Žoljom][@dingo-d] for the patches

### Changed
- GitHub releases will now always only contain unversioned release assets (PHARS + asc files) (same as it previously was in the squizlabs repo). See [#205] for context.
    - Thanks to [Shivam Mathur][@shivammathur] for opening a discussion about this
- Various housekeeping, includes improvements to the tests and documentation
    - Thanks to [Dan Wallis][@fredden], [Lucas Hoffmann][@lucc], [Rodrigo Primo][@rodrigoprimo] and [Juliette Reinders Folmer][@jrfnl] for their contributions

### Fixed
- Fixed bug [#124] : Report Full : avoid unnecessarily wrapping lines when `-s` is used
    - Thanks to [Brad Jorsch][@anomiex] for the patch
- Fixed bug [#124] : Report Full : fix incorrect bolding of pipes when `-s` is used and messages wraps
    - Thanks to [Brad Jorsch][@anomiex] for the patch
- Fixed bug [#150] : Squiz.WhiteSpace.KeywordSpacing : prevent a PHP notice when run during live coding
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#154] : Report Full : delimiter line calculation could go wonky on wide screens when a report contains multi-line messages
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#178] : Squiz.Commenting.VariableComment : docblocks were incorrectly being flagged as missing when a property declaration used PHP native union/intersection type declarations
    - Thanks to [Ferdinand Kuhl][@fcool] for the patch
- Fixed bug [#211] : Squiz.Commenting.VariableComment : docblocks were incorrectly being flagged as missing when a property declaration used PHP 8.2+ stand-alone `true`/`false`/`null` type declarations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#211] : Squiz.Commenting.VariableComment : docblocks were incorrectly being flagged as missing when a property declaration used PHP native `parent`, `self` or a namespace relative class name type declaration
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#226] : Generic.CodeAnalysis.ForLoopShouldBeWhileLoop : prevent a potential PHP 8.3 deprecation notice during live coding
    - Thanks to [Rodrigo Primo][@rodrigoprimo] for the patch

[#124]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/124
[#150]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/150
[#154]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/154
[#178]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/178
[#205]: https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/205
[#211]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/211
[#226]: https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/226

## [3.8.0] - 2023-12-08

[Squizlabs/PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is dead. Long live [PHPCSStandards/PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)!

### Breaking Changes
- The `squizlabs/PHP_CodeSniffer` repository has been abandoned. The `PHPCSStandards/PHP_CodeSniffer` repository will serve as the continuation of the project. For more information about this change, please read the [announcement](https://github.com/squizlabs/PHP_CodeSniffer/issues/3932).
    - Installation of PHP_CodeSniffer via PEAR is no longer supported.
        - Users will need to switch to another installation method.
        - Note: this does not affect the PEAR sniffs.
    - For Composer users, nothing changes.
        - **_In contrast to earlier information, the `squizlabs/php_codesniffer` package now points to the new repository and everything will continue to work as before._**
    - PHIVE users may need to clear the PHIVE URL cache.
        - PHIVE users who don't use the package alias, but refer to the package URL, will need to update the URL from `https://squizlabs.github.io/PHP_CodeSniffer/phars/` to `https://phars.phpcodesniffer.com/phars/`.
    - Users who download the PHAR files using curl or wget, will need to update the download URL from `https://squizlabs.github.io/PHP_CodeSniffer/[phpcs|phpcbf].phar` or `https://github.com/squizlabs/PHP_CodeSniffer/releases/latest/download/[phpcs|phpcbf].phar` to `https://phars.phpcodesniffer.com/[phpcs|phpcbf].phar`.
    - For users who install PHP_CodeSniffer via the [Setup-PHP](https://github.com/shivammathur/setup-php/) action runner for GitHub Actions, nothing changes.
    - Users using a git clone will need to update the clone address from `git@github.com:squizlabs/PHP_CodeSniffer.git` to `git@github.com:PHPCSStandards/PHP_CodeSniffer.git`.
        - Contributors will need to fork the new repo and add both the new fork as well as the new repo as remotes to their local git copy of PHP_CodeSniffer.
        - Users who have (valid) open issues or pull requests in the `squizlabs/PHP_CodeSniffer` repository are invited to resubmit these to the `PHPCSStandards/PHP_CodeSniffer` repository.

### Added
- Runtime support for PHP 8.3. All known PHP 8.3 deprecation notices have been fixed
    - Syntax support for new PHP 8.3 features will follow in a future release
    - If you find any PHP 8.3 deprecation notices which were missed, please report them
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches
- Added support for PHP 8.2 readonly classes to File::getClassProperties() through a new is_readonly array index in the return value
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for PHP 8.2 readonly classes to a number of sniffs
    - Generic.CodeAnalysis.UnnecessaryFinalModifier
    - PEAR.Commenting.ClassComment
    - PEAR.Commenting.FileComment
    - PSR1.Files.SideEffects
    - PSR2.Classes.ClassDeclaration
    - PSR12.Files.FileHeader
    - Squiz.Classes.ClassDeclaration
    - Squiz.Classes.LowercaseClassKeywords
    - Squiz.Commenting.ClassComment
    - Squiz.Commenting.DocCommentAlignment
    - Squiz.Commenting.FileComment
    - Squiz.Commenting.InlineComment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for PHP 8.2 `true` as a stand-alone type declaration
    - The `File::getMethodProperties()`, `File::getMethodParameters()` and `File::getMemberProperties()` methods now all support the `true` type
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for PHP 8.2 `true` as a stand-alone type to a number of sniffs
    - Generic.PHP.LowerCaseType
    - PSr12.Functions.NullableTypeDeclaration
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added a Performance report to allow for finding "slow" sniffs
    - To run this report, run PHPCS with --report=Performance.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.RequireStrictTypes : new warning for when there is a declare statement, but the strict_types directive is set to 0
    - The warning can be turned off by excluding the `Generic.PHP.RequireStrictTypes.Disabled` error code
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.FunctionComment : new `ParamNameUnexpectedAmpersandPrefix` error for parameters annotated as passed by reference while the parameter is not passed by reference
    - Thanks to [Dan Wallis][@fredden] for the patch
- Documentation has been added for the following sniffs:
    - PSR2.Files.ClosingTag
    - PSR2.Methods.FunctionCallSignature
    - PSR2.Methods.FunctionClosingBrace
    - Thanks to [Atsushi Okui][@blue32a] for the patch
- Support for PHPUnit 8 and 9 to the test suite
    - Test suites for external standards which run via the PHPCS native test suite can now run on PHPUnit 4-9 (was 4-7)
    - If any of these tests use the PHPUnit `setUp()`/`tearDown()` methods or overload the `setUp()` in the `AbstractSniffUnitTest` test case, they will need to be adjusted. See the [PR details for further information](https://github.com/PHPCSStandards/PHP_CodeSniffer/pull/59/commits/bc302dd977877a22c5e60d42a2f6b7d9e9192dab)
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Changed
- Changes have been made to the way PHPCS handles invalid sniff properties being set in a custom ruleset
    - Fixes PHP 8.2 deprecation notices for properties set in a (custom) ruleset for complete standards/complete sniff categories
    - Invalid sniff properties set for individual sniffs will now result in an error and halt the execution of PHPCS
        - A descriptive error message is provided to allow users to fix their ruleset
    - Sniff properties set for complete standards/complete sniff categories will now only be set on sniffs which explicitly support the property
        - The property will be silently ignored for those sniffs which do not support the property
    - Invalid sniff properties set for sniffs via inline annotations will result in an informative `Internal.PropertyDoesNotExist` errror on line 1 of the scanned file, but will not halt the execution of PHPCS
    - For sniff developers, it is strongly recommended for sniffs to explicitly declare any user-adjustable public properties
        - If dynamic properties need to be supported for a sniff, either declare the magic __set()/__get()/__isset()/__unset() methods on the sniff or let the sniff extend stdClass
        - Note: The `#[\AllowDynamicProperties]` attribute will have no effect for properties which are being set in rulesets
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The third parameter for the Ruleset::setSniffProperty() method has been changed to expect an array
    - Sniff developers/integrators of PHPCS may need to make some small adjustments to allow for this change
    - Existing code will continue to work but will throw a deprecation error
    - The backwards compatiblity layer will be removed in PHPCS 4.0
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- When using `auto` report width (the default) a value of 80 columns will be used if the width cannot be determined
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Sniff error messages are now more informative to help bugs get reported to the correct project
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.CodeAnalysis.UnusedFunctionParameter will now ignore magic methods for which the signature is defined by PHP
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Functions.OpeningFunctionBraceBsdAllman will now check the brace indent before the opening brace for empty functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Functions.OpeningFunctionBraceKernighanRitchie will now check the spacing before the opening brace for empty functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.WhiteSpace.IncrementDecrementSpacing now detects more spacing issues
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PSR2.Classes.PropertyDeclaration now enforces that the readonly modifier comes after the visibility modifier
    - PSR2 and PSR12 do not have documented rules for this as they pre-date the readonly modifier
    - PSR-PER has been used to confirm the order of this keyword so it can be applied to PSR2 and PSR12 correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PEAR.Commenting.FunctionComment + Squiz.Commenting.FunctionComment: the SpacingAfter error can now be auto-fixed
    - Thanks to [Dan Wallis][@fredden] for the patch
- Squiz.PHP.InnerFunctions sniff no longer reports on OO methods for OO structures declared within a function or closure
    - Thanks to [@Daimona] for the patch
- Squiz.PHP.NonExecutableCode will now also flag redundant return statements just before a closure close brace
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Runtime performance improvement for PHPCS CLI users. The improvement should be most noticeable for users on Windows.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The following sniffs have received performance related improvements:
    - Generic.PHP.LowerCaseConstant
    - Generic.PHP.LowerCaseType
    - PSR12.Files.OpenTag
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches
- The -e (explain) command will now list sniffs in natural order
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Tests using the PHPCS native test framework with multiple test case files will now run the test case files in numeric order.
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The following sniffs have received minor message readability improvements:
    - Generic.Arrays.ArrayIndent
    - Generic.Formatting.SpaceAfterCast
    - Generic.Formatting.SpaceAfterNot
    - Generic.WhiteSpace.SpreadOperatorSpacingAfter
    - Squiz.Arrays.ArrayDeclaration
    - Squiz.Commenting.DocCommentAlignment
    - Squiz.ControlStructures.ControlSignature
    - Thanks to [Danny van der Sluijs][@DannyvdSluijs] and [Juliette Reinders Folmer][@jrfnl] for the patches
- Improved README syntax highlighting
    - Thanks to [Benjamin Loison][@Benjamin-Loison] for the patch
- Various documentation improvements
    - Thanks to [Andrew Dawes][@AndrewDawes], [Danny van der Sluijs][@DannyvdSluijs] and [Juliette Reinders Folmer][@jrfnl] for the patches

### Removed
- Removed support for installation via PEAR
    - Use composer or the PHAR files instead

### Fixed
- Fixed bug [#2857][sq-2857] : Squiz/NonExecutableCode: prevent false positives when exit is used in a ternary expression or as default with null coalesce
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3386][sq-3386] : PSR1/SideEffects : improved recognition of disable/enable annotations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3557][sq-3557] : Squiz.Arrays.ArrayDeclaration will now ignore PHP 7.4 array unpacking when determining whether an array is associative
    - Thanks to [Volker Dusch][@edorian] for the patch
- Fixed bug [#3592][sq-3592] : Squiz/NonExecutableCode: prevent false positives when a PHP 8.0+ inline throw expression is encountered
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3715][sq-3715] : Generic/UnusedFunctionParameter: fixed incorrect errorcode for closures/arrow functions nested within extended classes/classes which implement
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3717][sq-3717] : Squiz.Commenting.FunctionComment: fixed false positive for `InvalidNoReturn` when type is never
    - Thanks to [Choraimy Kroonstuiver][@axlon] for the patch
- Fixed bug [#3720][sq-3720] : Generic/RequireStrictTypes : will now bow out silently in case of parse errors/live coding instead of throwing false positives/false negatives
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3720][sq-3720] : Generic/RequireStrictTypes : did not handle multi-directive declare statements
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3722][sq-3722] : Potential "Uninitialized string offset 1" in octal notation backfill
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3736][sq-3736] : PEAR/FunctionDeclaration: prevent fixer removing the close brace (and creating a parse error) when there is no space between the open brace and close brace of a function
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3739][sq-3739] : PEAR/FunctionDeclaration: prevent fixer conflict, and potentially creating a parse error, for unconventionally formatted return types
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3770][sq-3770] : Squiz/NonExecutableCode: prevent false positives for switching between PHP and HTML
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#3773][sq-3773] : Tokenizer/PHP: tokenization of the readonly keyword when used in combination with PHP 8.2 disjunctive normal types
    - Thanks to [Dan Wallis][@fredden] and [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3776][sq-3776] : Generic/JSHint: error when JSHint is not available
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#3777][sq-3777] : Squiz/NonExecutableCode: slew of bug fixes, mostly related to modern PHP
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3778][sq-3778] : Squiz/LowercasePHPFunctions: bug fix for class names in attributes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3779][sq-3779] : Generic/ForbiddenFunctions: bug fix for class names in attributes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3785][sq-3785] : Squiz.Commenting.FunctionComment: potential "Uninitialized string offset 0" when a type contains a duplicate pipe symbol
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#3787][sq-3787] : `PEAR/Squiz/[MultiLine]FunctionDeclaration`: allow for PHP 8.1 new in initializers
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3789][sq-3789] : Incorrect tokenization for ternary operator with `match` inside of it
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3790][sq-3790] : PSR12/AnonClassDeclaration: prevent fixer creating parse error when there was no space before the open brace
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3797][sq-3797] : Tokenizer/PHP: more context sensitive keyword fixes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3801][sq-3801] : File::getMethodParameters(): allow for readonly promoted properties without visibility
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3805][sq-3805] : Generic/FunctionCallArgumentSpacing: prevent fixer conflict over PHP 7.3+ trailing comma's in function calls
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3806][sq-3806] : Squiz.PHP.InnerFunctions sniff now correctly reports inner functions declared within a closure
    - Thanks to [@Daimona] for the patch
- Fixed bug [#3809][sq-3809] : GitBlame report was broken when passing a basepath
    - Thanks to [Chris][@datengraben] for the patch
- Fixed bug [#3813][sq-3813] : Squiz.Commenting.FunctionComment: false positive for parameter name mismatch on parameters annotated as passed by reference
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#3833][sq-3833] : Generic.PHP.LowerCaseType: fixed potential undefined array index notice
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3846][sq-3846] : PSR2.Classes.ClassDeclaration.CloseBraceAfterBody : fixer will no longer remove indentation on the close brace line
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3854][sq-3854] : Fatal error when using Gitblame report in combination with `--basepath` and running from project subdirectory
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3856][sq-3856] : PSR12.Traits.UseDeclaration was using the wrong error code - SpacingAfterAs - for spacing issues after the `use` keyword
    - These will now be reported using the SpacingAfterUse error code
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3856][sq-3856] : PSR12.Traits.UseDeclaration did not check spacing after `use` keyword for multi-line trait use statements
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3867][sq-3867] : Tokenizer/PHP: union type and intersection type operators were not correctly tokenized for static properties without explicit visibility
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3877][sq-3877] : Filter names can be case-sensitive. The -h help text will now display the correct case for the available filters
    - Thanks to [@simonsan] for the patch
- Fixed bug [#3893][sq-3893] : Generic/DocComment : the SpacingAfterTagGroup fixer could accidentally remove ignore annotations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3898][sq-3898] : Squiz/NonExecutableCode : the sniff could get confused over comments in unexpected places
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3904][sq-3904] : Squiz/FunctionSpacing : prevent potential fixer conflict
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3906][sq-3906] : Tokenizer/CSS: bug fix related to the unsupported slash comment syntax
    - Thanks to [Dan Wallis][@fredden] for the patch
- Fixed bug [#3913][sq-3913] : Config did not always correctly store unknown "long" arguments in the `$unknown` property
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

Thanks go to [Dan Wallis][@fredden] and [Danny van der Sluijs][@DannyvdSluijs] for reviewing quite a few of the PRs for this release.
Additionally, thanks to [Alexander Turek][@derrabus] for consulting on the repo change over.

[sq-2857]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2857
[sq-3386]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3386
[sq-3557]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3557
[sq-3592]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3592
[sq-3715]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3715
[sq-3717]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3717
[sq-3720]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3720
[sq-3722]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3722
[sq-3736]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3736
[sq-3739]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3739
[sq-3770]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3770
[sq-3773]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3773
[sq-3776]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3776
[sq-3777]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3777
[sq-3778]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3778
[sq-3779]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3779
[sq-3785]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3785
[sq-3787]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3787
[sq-3789]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3789
[sq-3790]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3790
[sq-3797]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3797
[sq-3801]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3801
[sq-3805]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3805
[sq-3806]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3806
[sq-3809]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3809
[sq-3813]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3813
[sq-3833]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3833
[sq-3846]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3846
[sq-3854]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3854
[sq-3856]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3856
[sq-3867]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3867
[sq-3877]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3877
[sq-3893]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3893
[sq-3898]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3898
[sq-3904]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3904
[sq-3906]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3906
[sq-3913]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3913

## [3.7.2] - 2023-02-23

### Changed
- Newer versions of Composer will now suggest installing PHPCS using require-dev instead of require
    - Thanks to [Gary Jones][@GaryJones] for the patch
- A custom Out Of Memory error will now be shown if PHPCS or PHPCBF run out of memory during a run
    - Error message provides actionable information about how to fix the problem and ensures the error is not silent
    - Thanks to [Juliette Reinders Folmer][@jrfnl] and [Alain Schlesser][@schlessera] for the patch
- Generic.PHP.LowerCaseType sniff now correctly examines types inside arrow functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Formatting.OperatorBracket no longer reports false positives in match() structures

### Fixed
- Fixed bug [#3616][sq-3616] : Squiz.PHP.DisallowComparisonAssignment false positive for PHP 8 match expression
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3618][sq-3618] : Generic.WhiteSpace.ArbitraryParenthesesSpacing false positive for return new parent()
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3632][sq-3632] : Short list not tokenized correctly in control structures without braces
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3639][sq-3639] : Tokenizer not applying tab replacement to heredoc/nowdoc closers
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3640][sq-3640] : Generic.WhiteSpace.DisallowTabIndent not reporting errors for PHP 7.3 flexible heredoc/nowdoc syntax
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3645][sq-3645] : PHPCS can show 0 exit code when running in parallel even if child process has fatal error
    - Thanks to [Alex Panshin][@enl] for the patch
- Fixed bug [#3653][sq-3653] : False positives for match() in OperatorSpacingSniff
    - Thanks to [Jaroslav Hanslík][@kukulich] for the patch
- Fixed bug [#3666][sq-3666] : PEAR.Functions.FunctionCallSignature incorrect indent fix when checking mixed HTML/PHP files
- Fixed bug [#3668][sq-3668] : PSR12.Classes.ClassInstantiation.MissingParentheses false positive when instantiating parent classes
    - Similar issues also fixed in Generic.Functions.FunctionCallArgumentSpacing and Squiz.Formatting.OperatorBracket
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3672][sq-3672] : Incorrect ScopeIndent.IncorrectExact report for match inside array literal
- Fixed bug [#3694][sq-3694] : Generic.WhiteSpace.SpreadOperatorSpacingAfter does not ignore spread operator in PHP 8.1 first class   callables
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-3616]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3616
[sq-3618]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3618
[sq-3632]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3632
[sq-3639]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3639
[sq-3640]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3640
[sq-3645]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3645
[sq-3653]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3653
[sq-3666]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3666
[sq-3668]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3668
[sq-3672]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3672
[sq-3694]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3694

## [3.7.1] - 2022-06-18

### Fixed
- Fixed bug [#3609][sq-3609] : Methods/constants with name empty/isset/unset are always reported as error
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-3609]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3609

## [3.7.0] - 2022-06-13

### Added
- Added support for PHP 8.1 explicit octal notation
    - This new syntax has been backfilled for PHP versions less than 8.1
    - Thanks to [Mark Baker][@MarkBaker] for the patch
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for additional fixes
- Added support for PHP 8.1 enums
    - This new syntax has been backfilled for PHP versions less than 8.1
    - Includes a new T_ENUM_CASE token to represent the case statements inside an enum
    - Thanks to [Jaroslav Hanslík][@kukulich] for the patch
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for additional core and sniff support
- Added support for the PHP 8.1 readonly token
    - Tokenizing of the readonly keyword has been backfilled for PHP versions less than 8.1
    - Thanks to [Jaroslav Hanslík][@kukulich] for the patch
- Added support for PHP 8.1 intersection types
    - Includes a new T_TYPE_INTERSECTION token to represent the ampersand character inside intersection types
    - Thanks to [Jaroslav Hanslík][@kukulich] for the patch

### Changed
- File::getMethodParameters now supports the new PHP 8.1 readonly token
    - When constructor property promotion is used, a new property_readonly array index is included in the return value
        - This is a boolean value indicating if the property is readonly
    - If the readonly token is detected, a new readonly_token array index is included in the return value
        - This contains the token index of the readonly keyword
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Support for new PHP 8.1 readonly keyword has been added to the following sniffs:
    - Generic.PHP.LowerCaseKeyword
    - PSR2.Classes.PropertyDeclaration
    - Squiz.Commenting.BlockComment
    - Squiz.Commenting.DocCommentAlignment
    - Squiz.Commenting.VariableComment
    - Squiz.WhiteSpace.ScopeKeywordSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patches
- The parallel feature is now more efficient and runs faster in some situations due to improved process management
    - Thanks to [Sergei Morozov][@morozov] for the patch
- The list of installed coding standards now has consistent ordering across all platforms
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.UpperCaseConstant and Generic.PHP.LowerCaseConstant now ignore type declarations
    - These sniffs now only report errors for true/false/null when used as values
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.LowerCaseType now supports the PHP 8.1 never type
    - Thanks to [Jaroslav Hanslík][@kukulich] for the patch

### Fixed
- Fixed bug [#3502][sq-3502] : A match statement within an array produces Squiz.Arrays.ArrayDeclaration.NoKeySpecified
- Fixed bug [#3503][sq-3503] : Squiz.Commenting.FunctionComment.ThrowsNoFullStop false positive when one line @throw
- Fixed bug [#3505][sq-3505] : The nullsafe operator is not counted in Generic.Metrics.CyclomaticComplexity
    - Thanks to [Mark Baker][@MarkBaker] for the patch
- Fixed bug [#3526][sq-3526] : PSR12.Properties.ConstantVisibility false positive when using public final const syntax
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3530][sq-3530] : Line indented incorrectly false positive when using match-expression inside switch case
- Fixed bug [#3534][sq-3534] : Name of typed enum tokenized as T_GOTO_LABEL
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3546][sq-3546] : Tokenizer/PHP: bug fix - parent/static keywords in class instantiations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3550][sq-3550] : False positive from PSR2.ControlStructures.SwitchDeclaration.TerminatingComment when using trailing   comment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3575][sq-3575] :  Squiz.Scope.MethodScope misses visibility keyword on previous line
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3604][sq-3604] :  Tokenizer/PHP: bug fix for double quoted strings using ${
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-3502]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3502
[sq-3503]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3503
[sq-3505]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3505
[sq-3526]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3526
[sq-3530]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3530
[sq-3534]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3534
[sq-3546]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3546
[sq-3550]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3550
[sq-3575]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3575
[sq-3604]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3604

## [3.6.2] - 2021-12-13

### Changed
- Processing large code bases that use tab indenting inside comments and strings will now be faster
    - Thanks to [Thiemo Kreuz][@thiemowmde] for the patch

### Fixed
- Fixed bug [#3388][sq-3388] : phpcs does not work when run from WSL drives
    - Thanks to [Juliette Reinders Folmer][@jrfnl] and [Graham Wharton][@gwharton] for the patch
- Fixed bug [#3422][sq-3422] : Squiz.WhiteSpace.ScopeClosingBrace fixer removes HTML content when fixing closing brace alignment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3437][sq-3437] : PSR12 does not forbid blank lines at the start of the class body
    - Added new PSR12.Classes.OpeningBraceSpace sniff to enforce this
- Fixed bug [#3440][sq-3440] : Squiz.WhiteSpace.MemberVarSpacing false positives when attributes used without docblock
    - Thanks to [Vadim Borodavko][@javer] for the patch
- Fixed bug [#3448][sq-3448] : PHP 8.1 deprecation notice while generating running time value
    - Thanks to [Juliette Reinders Folmer][@jrfnl] and [Andy Postnikov][@andypost] for the patch
- Fixed bug [#3456][sq-3456] : PSR12.Classes.ClassInstantiation.MissingParentheses false positive using attributes on anonymous class
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3460][sq-3460] : Generic.Formatting.MultipleStatementAlignment false positive on closure with parameters
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3468][sq-3468] : do/while loops are double-counted in Generic.Metrics.CyclomaticComplexity
    - Thanks to [Mark Baker][@MarkBaker] for the patch
- Fixed bug [#3469][sq-3469] : Ternary Operator and Null Coalescing Operator are not counted in Generic.Metrics.CyclomaticComplexity
    - Thanks to [Mark Baker][@MarkBaker] for the patch
- Fixed bug [#3472][sq-3472] : PHP 8 match() expression is not counted in Generic.Metrics.CyclomaticComplexity
    - Thanks to [Mark Baker][@MarkBaker] for the patch

[sq-3388]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3388
[sq-3422]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3422
[sq-3437]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3437
[sq-3440]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3440
[sq-3448]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3448
[sq-3456]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3456
[sq-3460]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3460
[sq-3468]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3468
[sq-3469]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3469
[sq-3472]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3472

## [3.6.1] - 2021-10-11

### Changed
- PHPCS annotations can now be specified using hash-style comments
    - Previously, only slash-style and block-style comments could be used to do things like disable errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The new PHP 8.1 tokenization for ampersands has been reverted to use the existing PHP_CodeSniffer method
    - The PHP 8.1 tokens T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG and T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG are unused
    - Ampersands continue to be tokenized as T_BITWISE_AND for all PHP versions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] and [Anna Filina][@afilina] for the patch
- File::getMethodParameters() no longer incorrectly returns argument attributes in the type hint array index
    - A new has_attributes array index is available and set to TRUE if the argument has attributes defined
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed an issue where some sniffs would not run on PHP files that only used the short echo tag
    - The following sniffs were affected:
        - Generic.Files.ExecutableFile
        - Generic.Files.LowercasedFilename
        - Generic.Files.LineEndings
        - Generic.Files.EndFileNewline
        - Generic.Files.EndFileNoNewline
        - Generic.PHP.ClosingPHPTag
        - Generic.PHP.Syntax
        - Generic.VersionControl.GitMergeConflict
        - Generic.WhiteSpace.DisallowSpaceIndent
        - Generic.WhiteSpace.DisallowTabIndent
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.BlockComment now correctly applies rules for block comments after a short echo tag
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Generic.NamingConventions.ConstructorName no longer throws deprecation notices on PHP 8.1
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed false positives when using attributes in the following sniffs:
    - PEAR.Commenting.FunctionComment
    - Squiz.Commenting.InlineComment
    - Squiz.Commenting.BlockComment
    - Squiz.Commenting.VariableComment
    - Squiz.WhiteSpace.MemberVarSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3294][sq-3294] : Bug in attribute tokenization when content contains PHP end token or attribute closer on new line
    - Thanks to [Alessandro Chitolina][@alekitto] for the patch
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the tests
- Fixed bug [#3296][sq-3296] : PSR2.ControlStructures.SwitchDeclaration takes phpcs:ignore as content of case body
- Fixed bug [#3297][sq-3297] : PSR2.ControlStructures.SwitchDeclaration.TerminatingComment does not handle try/finally blocks
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3302][sq-3302] : PHP 8.0 | Tokenizer/PHP: bugfix for union types using namespace operator
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3303][sq-3303] : findStartOfStatement() doesn't work with T_OPEN_TAG_WITH_ECHO
- Fixed bug [#3316][sq-3316] : Arrow function not tokenized correctly when using null in union type
- Fixed bug [#3317][sq-3317] : Problem with how phpcs handles ignored files when running in parallel
    - Thanks to [Emil Andersson][@emil-nasso] for the patch
- Fixed bug [#3324][sq-3324] : PHPCS hangs processing some nested arrow functions inside a function call
- Fixed bug [#3326][sq-3326] : Generic.Formatting.MultipleStatementAlignment error with const DEFAULT
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3333][sq-3333] : Squiz.Objects.ObjectInstantiation: null coalesce operators are not recognized as assignment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3340][sq-3340] : Ensure interface and trait names are always tokenized as T_STRING
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3342][sq-3342] : PSR12/Squiz/PEAR standards all error on promoted properties with docblocks
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3345][sq-3345] : IF statement with no braces and double catch turned into syntax error by auto-fixer
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3352][sq-3352] : PSR2.ControlStructures.SwitchDeclaration can remove comments on the same line as the case statement while fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3357][sq-3357] : Generic.Functions.OpeningFunctionBraceBsdAllman removes return type when additional lines are present
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3362][sq-3362] : Generic.WhiteSpace.ScopeIndent false positive for arrow functions inside arrays
- Fixed bug [#3384][sq-3384] : Squiz.Commenting.FileComment.SpacingAfterComment false positive on empty file
- Fixed bug [#3394][sq-3394] : Fix PHP 8.1 auto_detect_line_endings deprecation notice
- Fixed bug [#3400][sq-3400] : PHP 8.1: prevent deprecation notices about missing return types
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3424][sq-3424] : PHPCS fails when using PHP 8 Constructor property promotion with attributes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3425][sq-3425] : PHP 8.1 | Runner::processChildProcs(): fix passing null to non-nullable bug
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3445][sq-3445] : Nullable parameter after attribute incorrectly tokenized as ternary operator
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-3294]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3294
[sq-3296]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3296
[sq-3297]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3297
[sq-3302]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3302
[sq-3303]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3303
[sq-3316]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3316
[sq-3317]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3317
[sq-3324]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3324
[sq-3326]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3326
[sq-3333]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3333
[sq-3340]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3340
[sq-3342]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3342
[sq-3345]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3345
[sq-3352]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3352
[sq-3357]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3357
[sq-3362]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3362
[sq-3384]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3384
[sq-3394]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3394
[sq-3400]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3400
[sq-3424]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3424
[sq-3425]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3425
[sq-3445]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3445

## [3.6.0] - 2021-04-09

### Added
- Added support for PHP 8.0 union types
    - A new T_TYPE_UNION token is available to represent the pipe character
    - File::getMethodParameters(), getMethodProperties(), and getMemberProperties() will now return union types
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for PHP 8.0 named function call arguments
    - A new T_PARAM_NAME token is available to represent the label with the name of the function argument in it
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for PHP 8.0 attributes
    - The PHP-supplied T_ATTRIBUTE token marks the start of an attribute
    - A new T_ATTRIBUTE_END token is available to mark the end of an attribute
    - New attribute_owner and attribute_closer indexes are available in the tokens array for all tokens inside an attribute
    - Tokenizing of attributes has been backfilled for older PHP versions
    - The following sniffs have been updated to support attributes:
        - PEAR.Commenting.ClassComment
        - PEAR.Commenting.FileComment
        - PSR1.Files.SideEffects
        - PSR12.Files.FileHeader
        - Squiz.Commenting.ClassComment
        - Squiz.Commenting.FileComment
        - Squiz.WhiteSpace.FunctionSpacing
            - Thanks to [Vadim Borodavko][@javer] for the patch
    - Thanks to [Alessandro Chitolina][@alekitto] for the patch
- Added support for PHP 8.0 dereferencing of text strings with interpolated variables
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for PHP 8.0 match expressions
    - Match expressions are now tokenized with parenthesis and scope openers and closers
        - Sniffs can listen for the T_MATCH token to process match expressions
        - Note that the case and default statements inside match expressions do not have scopes set
    - A new T_MATCH_ARROW token is available to represent the arrows in match expressions
    - A new T_MATCH_DEFAULT token is available to represent the default keyword in match expressions
    - All tokenizing of match expressions has been backfilled for older PHP versions
    - The following sniffs have been updated to support match expressions:
        - Generic.CodeAnalysis.AssignmentInCondition
        - Generic.CodeAnalysis.EmptyPHPStatement
            - Thanks to [Vadim Borodavko][@javer] for the patch
        - Generic.CodeAnalysis.EmptyStatement
        - Generic.PHP.LowerCaseKeyword
        - PEAR.ControlStructures.ControlSignature
        - PSR12.ControlStructures.BooleanOperatorPlacement
        - Squiz.Commenting.LongConditionClosingComment
        - Squiz.Commenting.PostStatementComment
        - Squiz.ControlStructures.LowercaseDeclaration
        - Squiz.ControlStructures.ControlSignature
        - Squiz.Formatting.OperatorBracket
        - Squiz.PHP.DisallowMultipleAssignments
        - Squiz.Objects.ObjectInstantiation
        - Squiz.WhiteSpace.ControlStructureSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added Generic.NamingConventions.AbstractClassNamePrefix to enforce that class names are prefixed with "Abstract"
    - Thanks to [Anna Borzenko][@annechko] for the contribution
- Added Generic.NamingConventions.InterfaceNameSuffix to enforce that interface names are suffixed with "Interface"
    - Thanks to [Anna Borzenko][@annechko] for the contribution
- Added Generic.NamingConventions.TraitNameSuffix to enforce that trait names are suffixed with "Trait"
    - Thanks to [Anna Borzenko][@annechko] for the contribution

### Changed
- The value of the T_FN_ARROW token has changed from "T_FN_ARROW" to "PHPCS_T_FN_ARROW" to avoid package conflicts
    - This will have no impact on custom sniffs unless they are specifically looking at the value of the T_FN_ARROW constant
    - If sniffs are just using constant to find arrow functions, they will continue to work without modification
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- File::findStartOfStatement() now works correctly when passed the last token in a statement
- File::getMethodParameters() now supports PHP 8.0 constructor property promotion
    - Returned method params now include a "property_visibility" and "visibility_token" index if property promotion is detected
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- File::getMethodProperties() now includes a "return_type_end_token" index in the return value
    - This indicates the last token in the return type, which is helpful when checking union types
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Include patterns are now ignored when processing STDIN
    - Previously, checks using include patterns were excluded when processing STDIN when no file path was provided via --stdin-path
    - Now, all include and exclude rules are ignored when no file path is provided, allowing all checks to run
    - If you want include and exclude rules enforced when checking STDIN, use --stdin-path to set the file path
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Spaces are now correctly escaped in the paths to external on Windows
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.CodeAnalysis.UnusedFunctionParameter can now be configured to ignore variable usage for specific type hints
    - This allows you to suppress warnings for some variables that are not required, but leave warnings for others
    - Set the ignoreTypeHints array property to a list of type hints to ignore
    - Thanks to [Petr Bugyík][@o5] for the patch
- Generic.Formatting.MultipleStatementAlignment can now align statements at the start of the assignment token
    - Previously, the sniff enforced that the values were aligned, even if this meant the assignment tokens were not
    - Now, the sniff can enforce that the assignment tokens are aligned, even if this means the values are not
    - Set the "alignAtEnd" sniff property to "false" to align the assignment tokens
    - The default remains at "true", so the assigned values are aligned
    - Thanks to [John P. Bloch][@johnpbloch] for the patch
- Generic.PHP.LowerCaseType now supports checking of typed properties
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.LowerCaseType now supports checking of union types
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PEAR.Commenting.FunctionComment and Squiz.Commenting.FunctionComment sniffs can now ignore private and protected methods
    - Set the "minimumVisibility" sniff property to "protected" to ignore private methods
    - Set the "minimumVisibility" sniff property to "public" to ignore both private and protected methods
    - The default remains at "private", so all methods are checked
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- PEAR.Commenting.FunctionComment and Squiz.Commenting.FunctionComment sniffs can now ignore return tags in any method
    - Previously, only `__construct()` and `__destruct()` were ignored
    - Set the list of method names to ignore in the "specialMethods" sniff property
    - The default remains at "__construct" and "__destruct" only
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- PSR2.ControlStructures.SwitchDeclaration now supports nested switch statements where every branch terminates
    - Previously, if a CASE only contained a SWITCH and no direct terminating statement, a fall-through error was displayed
    - Now, the error is suppressed if every branch of the SWITCH has a terminating statement
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- The PSR2.Methods.FunctionCallSignature.SpaceBeforeCloseBracket error message is now reported on the closing parenthesis token
    - Previously, the error was being reported on the function keyword, leading to confusing line numbers in the error report
- Squiz.Commenting.FunctionComment is now able to ignore function comments that are only inheritdoc statements
    - Set the skipIfInheritdoc sniff property to "true" to skip checking function comments if the content is only {@inhertidoc}
    - The default remains at "false", so these comments will continue to report errors
    - Thanks to [Jess Myrbo][@xjm] for the patch
- Squiz.Commenting.FunctionComment now supports the PHP 8 mixed type
    - Thanks to [Vadim Borodavko][@javer] for the patch
- Squiz.PHP.NonExecutableCode now has improved handling of syntax errors
    - Thanks to [Thiemo Kreuz][@thiemowmde] for the patch
- Squiz.WhiteSpace.ScopeKeywordSpacing now checks spacing when using PHP 8.0 constructor property promotion
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed an issue that could occur when checking files on network drives, such as with WSL2 on Windows 10
    - This works around a long-standing PHP bug with is_readable()
    - Thanks to [Michael S][@codebymikey] for the patch
- Fixed a number of false positives in the Squiz.PHP.DisallowMultipleAssignments sniff
    - Sniff no longer errors for default value assignments in arrow functions
    - Sniff no longer errors for assignments on first line of closure
    - Sniff no longer errors for assignments after a goto label
    - Thanks to [Jaroslav Hanslík][@kukulich] for the patch
- Fixed bug [#2913][sq-2913] : Generic.WhiteSpace.ScopeIndent false positive when opening and closing tag on same line inside conditional
- Fixed bug [#2992][sq-2992] : Enabling caching using a ruleset produces invalid cache files when using --sniffs and --exclude CLI args
- Fixed bug [#3003][sq-3003] : Squiz.Formatting.OperatorBracket autofix incorrect when assignment used with null coalescing operator
- Fixed bug [#3145][sq-3145] : Autoloading of sniff fails when multiple classes declared in same file
- Fixed bug [#3157][sq-3157] : PSR2.ControlStructures.SwitchDeclaration.BreakIndent false positive when case keyword is not indented
- Fixed bug [#3163][sq-3163] : Undefined index error with pre-commit hook using husky on PHP 7.4
    - Thanks to [Ismo Vuorinen][@ivuorinen] for the patch
- Fixed bug [#3165][sq-3165] : Squiz.PHP.DisallowComparisonAssignment false positive when comparison inside closure
- Fixed bug [#3167][sq-3167] : Generic.WhiteSpace.ScopeIndent false positive when using PHP 8.0 constructor property promotion
- Fixed bug [#3170][sq-3170] : Squiz.WhiteSpace.OperatorSpacing false positive when using negation with string concat
    - This also fixes the same issue in the PSR12.Operators.OperatorSpacing sniff
- Fixed bug [#3177][sq-3177] : Incorrect tokenization of GOTO statements in mixed PHP/HTML files
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3184][sq-3184] : PSR2.Namespace.NamespaceDeclaration false positive on namespace operator
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3188][sq-3188] : Squiz.WhiteSpace.ScopeKeywordSpacing false positive for static return type
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3192][sq-3192] : findStartOfStatement doesn't work correctly inside switch
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- Fixed bug [#3195][sq-3195] : Generic.WhiteSpace.ScopeIndent confusing message when combination of tabs and spaces found
- Fixed bug [#3197][sq-3197] : Squiz.NamingConventions.ValidVariableName does not use correct error code for all member vars
- Fixed bug [#3219][sq-3219] : Generic.Formatting.MultipleStatementAlignment false positive for empty anonymous classes and closures
- Fixed bug [#3258][sq-3258] : Squiz.Formatting.OperatorBracket duplicate error messages for unary minus
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3273][sq-3273] : Squiz.Functions.FunctionDeclarationArgumentSpacing reports line break as 0 spaces between parenthesis
- Fixed bug [#3277][sq-3277] : Nullable static return typehint causes whitespace error
- Fixed bug [#3284][sq-3284] : Unused parameter false positive when using array index in arrow function

[sq-2913]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2913
[sq-2992]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2992
[sq-3003]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3003
[sq-3145]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3145
[sq-3157]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3157
[sq-3163]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3163
[sq-3165]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3165
[sq-3167]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3167
[sq-3170]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3170
[sq-3177]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3177
[sq-3184]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3184
[sq-3188]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3188
[sq-3192]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3192
[sq-3195]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3195
[sq-3197]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3197
[sq-3219]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3219
[sq-3258]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3258
[sq-3273]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3273
[sq-3277]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3277
[sq-3284]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3284

## [3.5.8] - 2020-10-23

### Removed
- Reverted a change to the way include/exclude patterns are processed for STDIN content
    - This change is not backwards compatible and will be re-introduced in version 3.6.0

## [3.5.7] - 2020-10-23

### Added
- The PHP 8.0 T_NULLSAFE_OBJECT_OPERATOR token has been made available for older versions
    - Existing sniffs that check for T_OBJECT_OPERATOR have been modified to apply the same rules for the nullsafe object operator
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The new method of PHP 8.0 tokenizing for namespaced names has been reverted to the pre 8.0 method
    - This maintains backwards compatible for existing sniffs on PHP 8.0
    - This change will be removed in PHPCS 4.0 as the PHP 8.0 tokenizing method will be backported for pre 8.0 versions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for changes to the way PHP 8.0 tokenizes hash comments
    - The existing PHP 5-7 behaviour has been replicated for version 8, so no sniff changes are required
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Running the unit tests now includes warnings in the found and fixable error code counts
      - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PSR12.Functions.NullableTypeDeclaration now supports the PHP8 static return type
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Changed
- The autoloader has been changed to fix sniff class name detection issues that may occur when running on PHP 7.4+
    - Thanks to [Eloy Lafuente][@stronk7] for the patch
- PSR12.ControlStructures.BooleanOperatorPlacement.FoundMixed error message is now more accurate when using the allowOnly setting
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch

### Fixed
- Fixed Squiz.Formatting.OperatorBracket false positive when exiting with a negative number
- Fixed Squiz.PHP.DisallowComparisonAssignment false positive for methods called on an object
- Fixed bug [#2882][sq-2882] : Generic.Arrays.ArrayIndent can request close brace indent to be less than the statement indent level
- Fixed bug [#2883][sq-2883] : Generic.WhiteSpace.ScopeIndent.Incorrect issue after NOWDOC
- Fixed bug [#2975][sq-2975] : Undefined offset in PSR12.Functions.ReturnTypeDeclaration when checking function return type inside ternary
- Fixed bug [#2988][sq-2988] : Undefined offset in Squiz.Strings.ConcatenationSpacing during live coding
    - Thanks to [Thiemo Kreuz][@thiemowmde] for the patch
- Fixed bug [#2989][sq-2989] : Incorrect auto-fixing in Generic.ControlStructures.InlineControlStructure during live coding
    - Thanks to [Thiemo Kreuz][@thiemowmde] for the patch
- Fixed bug [#3007][sq-3007] : Directory exclude pattern improperly excludes directories with names that start the same
    - Thanks to [Steve Talbot][@SteveTalbot] for the patch
- Fixed bug [#3043][sq-3043] : Squiz.WhiteSpace.OperatorSpacing false positive for negation in arrow function
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3049][sq-3049] : Incorrect error with arrow function and parameter passed as reference
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3053][sq-3053] : PSR2 incorrect fix when multiple use statements on same line do not have whitespace between them
- Fixed bug [#3058][sq-3058] : Progress gets unaligned when 100% happens at the end of the available dots
- Fixed bug [#3059][sq-3059] : Squiz.Arrays.ArrayDeclaration false positive when using type casting
    - Thanks to [Sergei Morozov][@morozov] for the patch
- Fixed bug [#3060][sq-3060] : Squiz.Arrays.ArrayDeclaration false positive for static functions
    - Thanks to [Sergei Morozov][@morozov] for the patch
- Fixed bug [#3065][sq-3065] : Should not fix Squiz.Arrays.ArrayDeclaration.SpaceBeforeComma if comment between element and comma
    - Thanks to [Sergei Morozov][@morozov] for the patch
- Fixed bug [#3066][sq-3066] : No support for namespace operator used in type declarations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3075][sq-3075] : PSR12.ControlStructures.BooleanOperatorPlacement false positive when operator is the only content on line
- Fixed bug [#3099][sq-3099] : Squiz.WhiteSpace.OperatorSpacing false positive when exiting with negative number
    - Thanks to [Sergei Morozov][@morozov] for the patch
- Fixed bug [#3102][sq-3102] : PSR12.Squiz.OperatorSpacing false positive for default values of arrow functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#3124][sq-3124] : PSR-12 not reporting error for empty lines with only whitespace
- Fixed bug [#3135][sq-3135] : Ignore annotations are broken on PHP 8.0
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-2882]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2882
[sq-2883]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2883
[sq-2975]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2975
[sq-2988]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2988
[sq-2989]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2989
[sq-3007]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3007
[sq-3043]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3043
[sq-3049]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3049
[sq-3053]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3053
[sq-3058]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3058
[sq-3059]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3059
[sq-3060]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3060
[sq-3065]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3065
[sq-3066]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3066
[sq-3075]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3075
[sq-3099]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3099
[sq-3102]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3102
[sq-3124]: https://github.com/squizlabs/PHP_CodeSniffer/issues/3124
[sq-3135]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3135

## [3.5.6] - 2020-08-10

### Added
- Added support for PHP 8.0 magic constant dereferencing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added support for changes to the way PHP 8.0 tokenizes comments
    - The existing PHP 5-7 behaviour has been replicated for version 8, so no sniff changes are required
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- `File::getMethodProperties()` now detects the PHP 8.0 static return type
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The PHP 8.0 static return type is now supported for arrow functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Changed
- The cache is no longer used if the list of loaded PHP extensions changes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- `Generic.NamingConventions.CamelCapsFunctionName` no longer reports `__serialize` and `__unserialize` as invalid names
    - Thanks to [Filip Š][@filips123] for the patch
- `PEAR.NamingConventions.ValidFunctionName` no longer reports `__serialize` and `__unserialize` as invalid names
    - Thanks to [Filip Š][@filips123] for the patch
- `Squiz.Scope.StaticThisUsage` now detects usage of `$this` inside closures and arrow functions
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch

### Fixed
- Fixed bug [#2877][sq-2877] : PEAR.Functions.FunctionCallSignature false positive for array of functions
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- Fixed bug [#2888][sq-2888] : PSR12.Files.FileHeader blank line error with multiple namespaces in one file
- Fixed bug [#2926][sq-2926] : phpcs hangs when using arrow functions that return heredoc
- Fixed bug [#2943][sq-2943] : Redundant semicolon added to a file when fixing PSR2.Files.ClosingTag.NotAllowed
- Fixed bug [#2967][sq-2967] : Markdown generator does not output headings correctly
    - Thanks to [Petr Bugyík][@o5] for the patch
- Fixed bug [#2977][sq-2977] : File::isReference() does not detect return by reference for closures
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2994][sq-2994] : Generic.Formatting.DisallowMultipleStatements false positive for FOR loop with no body
- Fixed bug [#3033][sq-3033] : Error generated during tokenizing of goto statements on PHP 8
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-2877]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2877
[sq-2888]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2888
[sq-2926]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2926
[sq-2943]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2943
[sq-2967]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2967
[sq-2977]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2977
[sq-2994]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2994
[sq-3033]: https://github.com/squizlabs/PHP_CodeSniffer/pull/3033

## [3.5.5] - 2020-04-17

### Changed
- The T_FN backfill now works more reliably so T_FN tokens only ever represent real arrow functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed an issue where including sniffs using paths containing multiple dots would silently fail
- Generic.CodeAnalysis.EmptyPHPStatement now detects empty statements at the start of control structures

### Fixed
- Error wording in PEAR.Functions.FunctionCallSignature now always uses "parenthesis" instead of sometimes using "bracket"
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- Fixed bug [#2787][sq-2787] : Squiz.PHP.DisallowMultipleAssignments not ignoring typed property declarations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2810][sq-2810] : PHPCBF fails to fix file with empty statement at start on control structure
- Fixed bug [#2812][sq-2812] : Squiz.Arrays.ArrayDeclaration not detecting some arrays with multiple arguments on the same line
    - Thanks to [Jakub Chábek][@grongor] for the patch
- Fixed bug [#2826][sq-2826] : Generic.WhiteSpace.ArbitraryParenthesesSpacing doesn't detect issues for statements directly after a control structure
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- Fixed bug [#2848][sq-2848] : PSR12.Files.FileHeader false positive for file with mixed PHP and HTML and no file header
- Fixed bug [#2849][sq-2849] : Generic.WhiteSpace.ScopeIndent false positive with arrow function inside array
- Fixed bug [#2850][sq-2850] : Generic.PHP.LowerCaseKeyword complains __HALT_COMPILER is uppercase
- Fixed bug [#2853][sq-2853] : Undefined variable error when using Info report
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2865][sq-2865] : Double arrow tokenized as T_STRING when placed after function named "fn"
- Fixed bug [#2867][sq-2867] : Incorrect scope matching when arrow function used inside IF condition
- Fixed bug [#2868][sq-2868] : phpcs:ignore annotation doesn't work inside a docblock
- Fixed bug [#2878][sq-2878] : PSR12.Files.FileHeader conflicts with Generic.Files.LineEndings
- Fixed bug [#2895][sq-2895] : PSR2.Methods.FunctionCallSignature.MultipleArguments false positive with arrow function argument

[sq-2787]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2787
[sq-2810]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2810
[sq-2812]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2812
[sq-2826]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2826
[sq-2848]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2848
[sq-2849]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2849
[sq-2850]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2850
[sq-2853]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2853
[sq-2865]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2865
[sq-2867]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2867
[sq-2868]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2868
[sq-2878]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2878
[sq-2895]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2895

## [3.5.4] - 2020-01-31

### Changed
- The PHP 7.4 numeric separator backfill now works correctly for more float formats
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The PHP 7.4 numeric separator backfill is no longer run on PHP version 7.4.0 or greater
- File::getCondition() now accepts a 3rd argument that allows for the closest matching token to be returned
    - By default, it continues to return the first matched token found from the top of the file
- Fixed detection of array return types for arrow functions
- Added Generic.PHP.DisallowRequestSuperglobal to ban the use of the $_REQUEST superglobal
    - Thanks to [Jeantwan Teuma][@Morerice] for the contribution
- Generic.ControlStructures.InlineControlStructure no longer shows errors for WHILE and FOR statements without a body
    - Previously it required these to have curly braces, but there were no statements to enclose in them
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PSR12.ControlStructures.BooleanOperatorPlacement can now be configured to enforce a specific operator position
    - By default, the sniff ensures that operators are all at the beginning or end of lines, but not a mix of both
    - Set the allowOnly property to "first" to enforce all boolean operators to be at the start of a line
    - Set the allowOnly property to "last" to enforce all boolean operators to be at the end of a line
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- PSR12.Files.ImportStatement now auto-fixes import statements by removing the leading slash
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Squiz.ControlStructures.ForLoopDeclaration now has a setting to ignore newline characters
    - Default remains FALSE, so newlines are not allowed within FOR definitions
    - Override the "ignoreNewlines" setting in a ruleset.xml file to change
- Squiz.PHP.InnerFunctions now handles multiple nested anon classes correctly

### Fixed
- Fixed bug [#2497][sq-2497] : Sniff properties not set when referencing a sniff using relative paths or non-native slashes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2657][sq-2657] : Squiz.WhiteSpace.FunctionSpacing can remove spaces between comment and first/last method during auto-fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2688][sq-2688] : Case statements not tokenized correctly when switch is contained within ternary
- Fixed bug [#2698][sq-2698] : PHPCS throws errors determining auto report width when shell_exec is disabled
    - Thanks to [Matthew Peveler][@MasterOdin] for the patch
- Fixed bug [#2730][sq-2730] : PSR12.ControlStructures.ControlStructureSpacing does not ignore comments between conditions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2732][sq-2732] : PSR12.Files.FileHeader misidentifies file header in mixed content file
- Fixed bug [#2745][sq-2745] : AbstractArraySniff wrong indices when mixed coalesce and ternary values
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#2748][sq-2748] : Wrong end of statement for fn closures
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#2751][sq-2751] : Autoload relative paths first to avoid confusion with files from the global include path
    - Thanks to [Klaus Purer][@klausi] for the patch
- Fixed bug [#2763][sq-2763] : PSR12 standard reports errors for multi-line FOR definitions
- Fixed bug [#2768][sq-2768] : Generic.Files.LineLength false positive for non-breakable strings at exactly the soft limit
    - Thanks to [Alex Miles][@ghostal] for the patch
- Fixed bug [#2773][sq-2773] : PSR2.Methods.FunctionCallSignature false positive when arrow function has array return type
- Fixed bug [#2790][sq-2790] : PSR12.Traits.UseDeclaration ignores block comments
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- Fixed bug [#2791][sq-2791] : PSR12.Functions.NullableTypeDeclaration false positive when ternary operator used with instanceof
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2802][sq-2802] : Can't specify a report file path using the tilde shortcut
- Fixed bug [#2804][sq-2804] : PHP4-style typed properties not tokenized correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2805][sq-2805] : Undefined Offset notice during live coding of arrow functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2843][sq-2843] : Tokenizer does not support alternative syntax for declare statements
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-2497]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2497
[sq-2657]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2657
[sq-2688]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2688
[sq-2698]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2698
[sq-2730]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2730
[sq-2732]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2732
[sq-2745]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2745
[sq-2748]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2748
[sq-2751]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2751
[sq-2763]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2763
[sq-2768]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2768
[sq-2773]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2773
[sq-2790]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2790
[sq-2791]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2791
[sq-2802]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2802
[sq-2804]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2804
[sq-2805]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2805
[sq-2843]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2843

## [3.5.3] - 2019-12-04

### Changed
- The PHP 7.4 T_FN token has been made available for older versions
    - T_FN represents the fn string used for arrow functions
    - The double arrow becomes the scope opener, and uses a new T_FN_ARROW token type
    - The token after the statement (normally a semicolon) becomes the scope closer
    - The token is also associated with the opening and closing parenthesis of the statement
    - Any functions named "fn" will have a T_FN token for the function name, but have no scope information
    - Thanks to [Michał Bundyra][@michalbundyra] for the help with this change
- PHP 7.4 numeric separators are now tokenized in the same way when using older PHP versions
    - Previously, a number like 1_000 would tokenize as T_LNUMBER (1), T_STRING (_000)
    - Now, the number tokenizes as T_LNUMBER (1_000)
    - Sniff developers should consider how numbers with underscores impact their custom sniffs
- The PHPCS file cache now takes file permissions into account
    - The cache is now invalidated for a file when its permissions are changed
- File::getMethodParameters() now supports arrow functions
- File::getMethodProperties() now supports arrow functions
- Added Fixer::changeCodeBlockIndent() to change the indent of a code block while auto-fixing
    - Can be used to either increase or decrease the indent
    - Useful when moving the start position of something like a closure, where you want the content to also move
- Added Generic.Files.ExecutableFile sniff
    - Ensures that files are not executable
    - Thanks to [Matthew Peveler][@MasterOdin] for the contribution
- Generic.CodeAnalysis.EmptyPhpStatement now reports unnecessary semicolons after control structure closing braces
    - Thanks to [Vincent Langlet][@VincentLanglet] for the patch
- Generic.PHP.LowerCaseKeyword now enforces that the "fn" keyword is lowercase
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Generic.WhiteSpace.ScopeIndent now supports static arrow functions
- PEAR.Functions.FunctionCallSignature now adjusts the indent of function argument contents during auto-fixing
    - Previously, only the first line of an argument was changed, leading to inconsistent indents
    - This change also applies to PSR2.Methods.FunctionCallSignature
- PSR2.ControlStructures.ControlStructureSpacing now checks whitespace before the closing parenthesis of multi-line control structures
    - Previously, it incorrectly applied the whitespace check for single-line definitions only
- PSR12.Functions.ReturnTypeDeclaration now checks the return type of arrow functions
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- PSR12.Traits.UseDeclaration now ensures all trait import statements are grouped together
    - Previously, the trait import section of the class ended when the first non-import statement was found
    - Checking now continues throughout the class to ensure all statements are grouped together
    - This also ensures that empty lines are not requested after an import statement that isn't the last one
- Squiz.Functions.LowercaseFunctionKeywords now enforces that the "fn" keyword is lowercase
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch

### Fixed
- Fixed bug [#2586][sq-2586] : Generic.WhiteSpace.ScopeIndent false positives when indenting open tags at a non tab-stop
- Fixed bug [#2638][sq-2638] : Squiz.CSS.DuplicateClassDefinitionSniff sees comments as part of the class name
    - Thanks to [Raphael Horber][@rhorber] for the patch
- Fixed bug [#2640][sq-2640] : Squiz.WhiteSpace.OperatorSpacing false positives for some negation operators
    - Thanks to [Jakub Chábek][@grongor] and [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2674][sq-2674] : Squiz.Functions.FunctionDeclarationArgumentSpacing prints wrong argument name in error message
- Fixed bug [#2676][sq-2676] : PSR12.Files.FileHeader locks up when file ends with multiple inline comments
- Fixed bug [#2678][sq-2678] : PSR12.Classes.AnonClassDeclaration incorrectly enforcing that closing brace be on a line by itself
- Fixed bug [#2685][sq-2685] : File::getMethodParameters() setting typeHintEndToken for vars with no type hint
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2694][sq-2694] : AbstractArraySniff produces invalid indices when using ternary operator
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#2702][sq-2702] : Generic.WhiteSpace.ScopeIndent false positive when using ternary operator with short arrays

[sq-2586]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2586
[sq-2638]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2638
[sq-2640]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2640
[sq-2674]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2674
[sq-2676]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2676
[sq-2678]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2678
[sq-2685]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2685
[sq-2694]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2694
[sq-2702]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2702

## [3.5.2] - 2019-10-28

### Changed
- Generic.ControlStructures.DisallowYodaConditions now returns less false positives
    - False positives were being returned for array comparisons, or when performing some function calls
- Squiz.WhiteSpace.SemicolonSpacing.Incorrect error message now escapes newlines and tabs
    - Provides a clearer error message as whitespace is now visible
    - Also allows for better output for report types such as CSV and XML
- The error message for PSR12.Files.FileHeader.SpacingAfterBlock has been made clearer
    - It now uses the wording from the published PSR-12 standard to indicate that blocks must be separated by a blank line
    - Thanks to [Craig Duncan][@duncan3dc] for the patch

### Fixed
- Fixed bug [#2654][sq-2654] : Incorrect indentation for arguments of multiline function calls
- Fixed bug [#2656][sq-2656] : Squiz.WhiteSpace.MemberVarSpacing removes comments before first member var during auto fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2663][sq-2663] : Generic.NamingConventions.ConstructorName complains about old constructor in interfaces
- Fixed bug [#2664][sq-2664] : PSR12.Files.OpenTag incorrectly identifies PHP file with only an opening tag
- Fixed bug [#2665][sq-2665] : PSR12.Files.ImportStatement should not apply to traits
- Fixed bug [#2673][sq-2673] : PSR12.Traits.UseDeclaration does not allow comments or blank lines between use statements

[sq-2654]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2654
[sq-2656]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2656
[sq-2663]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2663
[sq-2664]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2664
[sq-2665]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2665
[sq-2673]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2673

## [3.5.1] - 2019-10-17

### Changed
- Very very verbose diff report output has slightly changed to improve readability
    - Output is printed when running PHPCS with the --report=diff and -vvv command line arguments
    - Fully qualified class names have been replaced with sniff codes
    - Tokens being changed now display the line number they are on
- PSR2, PSR12, and PEAR standards now correctly check for blank lines at the start of function calls
    - This check has been missing from these standards, but has now been implemented
    - When using the PEAR standard, the error code is PEAR.Functions.FunctionCallSignature.FirstArgumentPosition
    - When using PSR2 or PSR12, the error code is PSR2.Methods.FunctionCallSignature.FirstArgumentPosition
- PSR12.ControlStructures.BooleanOperatorPlacement no longer complains when multiple expressions appear on the same line
    - Previously, boolean operators were enforced to appear at the start or end of lines only
    - Boolean operators can now appear in the middle of the line
- PSR12.Files.FileHeader no longer ignores comments preceding a use, namespace, or declare statement
- PSR12.Files.FileHeader now allows a hashbang line at the top of the file

### Fixed
- Fixed bug [#2506][sq-2506] : PSR2 standard can't auto fix multi-line function call inside a string concat statement
- Fixed bug [#2530][sq-2530] : PEAR.Commenting.FunctionComment does not support intersection types in comments
- Fixed bug [#2615][sq-2615] : Constant visibility false positive on non-class constants
- Fixed bug [#2616][sq-2616] : PSR12.Files.FileHeader false positive when file only contains docblock
- Fixed bug [#2619][sq-2619] : PSR12.Files.FileHeader locks up when inline comment is the last content in a file
- Fixed bug [#2621][sq-2621] : PSR12.Classes.AnonClassDeclaration.CloseBraceSameLine false positive for anon class passed as function argument
    - Thanks to [Martins Sipenko][@martinssipenko] for the patch
- Fixed bug [#2623][sq-2623] : PSR12.ControlStructures.ControlStructureSpacing not ignoring indentation inside multi-line string arguments
- Fixed bug [#2624][sq-2624] : PSR12.Traits.UseDeclaration doesnt apply the correct indent during auto fixing
- Fixed bug [#2626][sq-2626] : PSR12.Files.FileHeader detects @var annotations as file docblocks
- Fixed bug [#2628][sq-2628] : PSR12.Traits.UseDeclaration does not allow comments above a USE declaration
- Fixed bug [#2632][sq-2632] : Incorrect indentation of lines starting with "static" inside closures
- Fixed bug [#2641][sq-2641] : PSR12.Functions.NullableTypeDeclaration false positive when using new static()

[sq-2506]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2506
[sq-2530]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2530
[sq-2615]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2615
[sq-2616]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2616
[sq-2619]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2619
[sq-2621]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2621
[sq-2623]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2623
[sq-2624]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2624
[sq-2626]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2626
[sq-2628]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2628
[sq-2632]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2632
[sq-2641]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2641

## [3.5.0] - 2019-09-27

### Changed
- The included PSR12 standard is now complete and ready to use
    - Check your code using PSR-12 by running PHPCS with --standard=PSR12
- Added support for PHP 7.4 typed properties
    - The nullable operator is now tokenized as T_NULLABLE inside property types, as it is elsewhere
    - To get the type of a member var, use the File::getMemberProperties() method, which now contains a "type" array index
        - This contains the type of the member var, or a blank string if not specified
        - If the type is nullable, the return type will contain the leading ?
        - If a type is specified, the position of the first token in the type will be set in a "type_token" array index
        - If a type is specified, the position of the last token in the type will be set in a "type_end_token" array index
        - If the type is nullable, a "nullable_type" array index will also be set to TRUE
        - If the type contains namespace information, it will be cleaned of whitespace and comments in the return value
- The PSR1 standard now correctly bans alternate PHP tags
    - Previously, it only banned short open tags and not the pre-7.0 alternate tags
- Added support for only checking files that have been locally staged in a git repo
    - Use --filter=gitstaged to check these files
    - You still need to give PHPCS a list of files or directories in which to apply the filter
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- JSON reports now end with a newline character
- The phpcs.xsd schema now validates phpcs-only and phpcbf-only attributes correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The tokenizer now correctly identifies inline control structures in more cases
- All helper methods inside the File class now throw RuntimeException instead of TokenizerException
    - Some tokenizer methods were also throwing RuntimeException but now correctly throw TokenizerException
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The File::getMethodParameters() method now returns more information, and supports closure USE groups
    - If a type hint is specified, the position of the last token in the hint will be set in a "type_hint_end_token" array index
    - If a default is specified, the position of the first token in the default value will be set in a "default_token" array index
    - If a default is specified, the position of the equals sign will be set in a "default_equal_token" array index
    - If the param is not the last, the position of the comma will be set in a "comma_token" array index
    - If the param is passed by reference, the position of the reference operator will be set in a "reference_token" array index
    - If the param is variable length, the position of the variadic operator will be set in a "variadic_token" array index
- The T_LIST token and it's opening and closing parentheses now contain references to each other in the tokens array
    - Uses the same parenthesis_opener/closer/owner indexes as other tokens
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The T_ANON_CLASS token and it's opening and closing parentheses now contain references to each other in the tokens array
    - Uses the same parenthesis_opener/closer/owner indexes as other tokens
    - Only applicable if the anon class is passing arguments to the constructor
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The PHP 7.4 T_BAD_CHARACTER token has been made available for older versions
    - Allows you to safely look for this token, but it will not appear unless checking with PHP 7.4+
- Metrics are now available for Squiz.WhiteSpace.FunctionSpacing
    - Use the "info" report to see blank lines before/after functions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Metrics are now available for Squiz.WhiteSpace.MemberVarSpacing
    - Use the "info" report to see blank lines before member vars
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added Generic.ControlStructures.DisallowYodaConditions sniff
    - Ban the use of Yoda conditions
    - Thanks to [Mponos George][@gmponos] for the contribution
- Added Generic.PHP.RequireStrictTypes sniff
    - Enforce the use of a strict types declaration in PHP files
- Added Generic.WhiteSpace.SpreadOperatorSpacingAfter sniff
    - Checks whitespace between the spread operator and the variable/function call it applies to
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added PSR12.Classes.AnonClassDeclaration sniff
    - Enforces the formatting of anonymous classes
- Added PSR12.Classes.ClosingBrace sniff
    - Enforces that closing braces of classes/interfaces/traits/functions are not followed by a comment or statement
- Added PSR12.ControlStructures.BooleanOperatorPlacement sniff
    - Enforces that boolean operators between conditions are consistently at the start or end of the line
- Added PSR12.ControlStructures.ControlStructureSpacing sniff
    - Enforces that spacing and indents are correct inside control structure parenthesis
- Added PSR12.Files.DeclareStatement sniff
    - Enforces the formatting of declare statements within a file
- Added PSR12.Files.FileHeader sniff
    - Enforces the order and formatting of file header blocks
- Added PSR12.Files.ImportStatement sniff
    - Enforces the formatting of import statements within a file
- Added PSR12.Files.OpenTag sniff
    - Enforces that the open tag is on a line by itself when used at the start of a PHP-only file
- Added PSR12.Functions.ReturnTypeDeclaration sniff
    - Enforces the formatting of return type declarations in functions and closures
- Added PSR12.Properties.ConstantVisibility sniff
    - Enforces that constants must have their visibility defined
    - Uses a warning instead of an error due to this conditionally requiring the project to support PHP 7.1+
- Added PSR12.Traits.UseDeclaration sniff
    - Enforces the formatting of trait import statements within a class
- Generic.Files.LineLength ignoreComments property now ignores comments at the end of a line
    - Previously, this property was incorrectly causing the sniff to ignore any line that ended with a comment
    - Now, the trailing comment is not included in the line length, but the rest of the line is still checked
- Generic.Files.LineLength now only ignores unwrappable comments when the comment is on a line by itself
    - Previously, a short unwrappable comment at the end of the line would have the sniff ignore the entire line
- Generic.Functions.FunctionCallArgumentSpacing no longer checks spacing around assignment operators inside function calls
    - Use the Squiz.WhiteSpace.OperatorSpacing sniff to enforce spacing around assignment operators
        - Note that this sniff checks spacing around all assignment operators, not just inside function calls
    - The Generic.Functions.FunctionCallArgumentSpacing.NoSpaceBeforeEquals error has been removed
        - Use Squiz.WhiteSpace.OperatorSpacing.NoSpaceBefore instead
    - The Generic.Functions.FunctionCallArgumentSpacing.NoSpaceAfterEquals error has been removed
        - Use Squiz.WhiteSpace.OperatorSpacing.NoSpaceAfter instead
    - This also changes the PEAR/PSR2/PSR12 standards so they no longer check assignment operators inside function calls
        - They were previously checking these operators when they should not have
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.WhiteSpace.ScopeIndent no longer performs exact indents checking for chained method calls
    - Other sniffs can be used to enforce chained method call indent rules
    - Thanks to [Pieter Frenssen][@pfrenssen] for the patch
- PEAR.WhiteSpace.ObjectOperatorIndent now supports multi-level chained statements
    - When enabled, chained calls must be indented 1 level more or less than the previous line
    - Set the new "multilevel" setting to TRUE in a ruleset.xml file to enable this behaviour
    - Thanks to [Marcos Passos][@marcospassos] for the patch
- PSR2.ControlStructures.ControlStructureSpacing now allows whitespace after the opening parenthesis if followed by a comment
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- PSR2.Classes.PropertyDeclaration now enforces a single space after a property type keyword
    - The PSR2 standard itself excludes this new check as it is not defined in the written standard
    - Using the PSR12 standard will enforce this check
- Squiz.Commenting.BlockComment no longer requires blank line before comment if it's the first content after the PHP open tag
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Functions.FunctionDeclarationArgumentSpacing now has more accurate error messages
    - This includes renaming the SpaceAfterDefault error code to SpaceAfterEquals, which reflects the real error
- Squiz.Functions.FunctionDeclarationArgumentSpacing now checks for no space after a reference operator
    - If you don't want this new behaviour, exclude the SpacingAfterReference error message in a ruleset.xml file
- Squiz.Functions.FunctionDeclarationArgumentSpacing now checks for no space after a variadic operator
    - If you don't want this new behaviour, exclude the SpacingAfterVariadic error message in a ruleset.xml file
- Squiz.Functions.MultiLineFunctionDeclaration now has improved fixing for the FirstParamSpacing and UseFirstParamSpacing errors
- Squiz.Operators.IncrementDecrementUsage now suggests pre-increment of variables instead of post-increment
    - This change does not enforce pre-increment over post-increment; only the suggestion has changed
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.DisallowMultipleAssignments now has a second error code for when assignments are found inside control structure conditions
    - The new error code is Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
    - All other multiple assignment cases use the existing error code Squiz.PHP.DisallowMultipleAssignments.Found
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.FunctionSpacing now applies beforeFirst and afterLast spacing rules to nested functions
    - Previously, these rules only applied to the first and last function in a class, interface, or trait
    - These rules now apply to functions nested in any statement block, including other functions and conditions
- Squiz.WhiteSpace.OperatorSpacing now has improved handling of parse errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.OperatorSpacing now checks spacing around the instanceof operator
    - Thanks to [Jakub Chábek][@grongor] for the patch
- Squiz.WhiteSpace.OperatorSpacing can now enforce a single space before assignment operators
    - Previously, the sniff this spacing as multiple assignment operators are sometimes aligned
    - Now, you can set the ignoreSpacingBeforeAssignments sniff property to FALSE to enable checking
    - Default remains TRUE, so spacing before assignments is not checked by default
    - Thanks to [Jakub Chábek][@grongor] for the patch

### Fixed
- Fixed bug [#2391][sq-2391] : Sniff-specific ignore rules inside rulesets are filtering out too many files
    - Thanks to [Juliette Reinders Folmer][@jrfnl] and [Willington Vega][@wvega] for the patch
- Fixed bug [#2478][sq-2478] : FunctionCommentThrowTag.WrongNumber when exception is thrown once but built conditionally
- Fixed bug [#2479][sq-2479] : Generic.WhiteSpace.ScopeIndent error when using array destructing with exact indent checking
- Fixed bug [#2498][sq-2498] : Squiz.Arrays.ArrayDeclaration.MultiLineNotAllowed autofix breaks heredoc
- Fixed bug [#2502][sq-2502] : Generic.WhiteSpace.ScopeIndent false positives with nested switch indentation and case fall-through
- Fixed bug [#2504][sq-2504] : Generic.WhiteSpace.ScopeIndent false positives with nested arrays and nowdoc string
- Fixed bug [#2511][sq-2511] : PSR2 standard not checking if closing paren of single-line function declaration is on new line
- Fixed bug [#2512][sq-2512] : Squiz.PHP.NonExecutableCode does not support alternate SWITCH control structure
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2522][sq-2522] : Text generator throws error when code sample line is too long
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2526][sq-2526] : XML report format has bad syntax on Windows
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2529][sq-2529] : Generic.Formatting.MultipleStatementAlignment wrong error for assign in string concat
- Fixed bug [#2534][sq-2534] : Unresolvable installed_paths can lead to open_basedir errors
    - Thanks to [Oliver Nowak][@ndm2] for the patch
- Fixed bug [#2541][sq-2541] : Text doc generator does not allow for multi-line rule explanations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2549][sq-2549] : Searching for a phpcs.xml file can throw warnings due to open_basedir restrictions
    - Thanks to [Matthew Peveler][@MasterOdin] for the patch
- Fixed bug [#2558][sq-2558] : PHP 7.4 throwing offset syntax with curly braces is deprecated message
    - Thanks to [Matthew Peveler][@MasterOdin] for the patch
- Fixed bug [#2561][sq-2561] : PHP 7.4 compatibility fix / implode argument order
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2562][sq-2562] : Inline WHILE triggers SpaceBeforeSemicolon incorrectly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2565][sq-2565] : Generic.ControlStructures.InlineControlStructure confused by mixed short/long tags
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2566][sq-2566] : Author tag email validation doesn't support all TLDs
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2575][sq-2575] : Custom error messages don't have data replaced when cache is enabled
- Fixed bug [#2601][sq-2601] : Squiz.WhiteSpace.FunctionSpacing incorrect fix when spacing is 0
- Fixed bug [#2608][sq-2608] : PSR2 throws errors for use statements when multiple namespaces are defined in a file

[sq-2391]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2391
[sq-2478]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2478
[sq-2479]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2479
[sq-2498]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2498
[sq-2502]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2502
[sq-2504]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2504
[sq-2511]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2511
[sq-2512]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2512
[sq-2522]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2522
[sq-2526]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2526
[sq-2529]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2529
[sq-2534]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2534
[sq-2541]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2541
[sq-2549]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2549
[sq-2558]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2558
[sq-2561]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2561
[sq-2562]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2562
[sq-2565]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2565
[sq-2566]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2566
[sq-2575]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2575
[sq-2601]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2601
[sq-2608]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2608

## [3.4.2] - 2019-04-11

### Changed
- Squiz.Arrays.ArrayDeclaration now has improved handling of syntax errors

### Fixed
- Fixed an issue where the PCRE JIT on PHP 7.3 caused PHPCS to die when using the parallel option
    - PHPCS now disables the PCRE JIT before running
- Fixed bug [#2368][sq-2368] : MySource.PHP.AjaxNullComparison throws error when first function has no doc comment
- Fixed bug [#2414][sq-2414] : Indention false positive in switch/case/if combination
- Fixed bug [#2423][sq-2423] : Squiz.Formatting.OperatorBracket.MissingBrackets error with static
- Fixed bug [#2450][sq-2450] : Indentation false positive when closure containing nested IF conditions used as function argument
- Fixed bug [#2452][sq-2452] : LowercasePHPFunctions sniff failing on "new \File()"
- Fixed bug [#2453][sq-2453] : Squiz.CSS.SemicolonSpacingSniff false positive when style name proceeded by an asterisk
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2464][sq-2464] : Fixer conflict between Generic.WhiteSpace.ScopeIndent and Squiz.WhiteSpace.ScopeClosingBrace when class indented 1 space
- Fixed bug [#2465][sq-2465] : Excluding a sniff by path is not working
- Fixed bug [#2467][sq-2467] : PHP open/close tags inside CSS files are replaced with internal PHPCS token strings when auto fixing

[sq-2368]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2368
[sq-2414]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2414
[sq-2423]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2423
[sq-2450]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2450
[sq-2452]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2452
[sq-2453]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2453
[sq-2464]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2464
[sq-2465]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2465
[sq-2467]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2467

## [3.4.1] - 2019-03-19

### Changed
- The PEAR installable version of PHPCS was missing some files, which have been re-included in this release
    - The code report was not previously available for PEAR installs
    - The Generic.Formatting.SpaceBeforeCast sniff was not previously available for PEAR installs
    - The Generic.WhiteSpace.LanguageConstructSpacing sniff was not previously available for PEAR installs
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PHPCS will now refuse to run if any of the required PHP extensions are not loaded
    - Previously, PHPCS only relied on requirements being checked by PEAR and Composer
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Ruleset XML parsing errors are now displayed in a readable format so they are easier to correct
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The PSR2 standard no longer throws duplicate errors for spacing around FOR loop parentheses
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- T_PHPCS_SET tokens now contain sniffCode, sniffProperty, and sniffPropertyValue indexes
    - Sniffs can use this information instead of having to parse the token content manually
- Added more guard code for syntax errors to various CSS sniffs
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Commenting.DocComment error messages now contain the name of the comment tag that caused the error
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.ControlStructures.InlineControlStructure now handles syntax errors correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Debug.JSHint now longer requires rhino and can be run directly from the npm install
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Files.LineEndings no longer adds superfluous new line at the end of JS and CSS files
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Formatting.DisallowMultipleStatements no longer tries to fix lines containing phpcs:ignore statements
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Functions.FunctionCallArgumentSpacing now has improved performance and anonymous class support
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.WhiteSpace.ScopeIndent now respects changes to the "exact" property using phpcs:set mid-way through a file
    - This allows you to change the "exact" rule for only some parts of a file
- Generic.WhiteSpace.ScopeIndent now disables exact indent checking inside all arrays
    - Previously, this was only done when using long array syntax, but it now works for short array syntax as well
- PEAR.Classes.ClassDeclaration now has improved handling of PHPCS annotations and tab indents
- PSR12.Classes.ClassInstantiation has changed its error code from MissingParenthesis to MissingParentheses
- PSR12.Keywords.ShortFormTypeKeywords now ignores all spacing inside type casts during both checking and fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Classes.LowercaseClassKeywords now examines the class keyword for anonymous classes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.ControlStructures.ControlSignature now has improved handling of parse errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.PostStatementComment fixer no longer adds a blank line at the start of a JS file that begins with a comment
    - Fixes a conflict between this sniff and the Squiz.WhiteSpace.SuperfluousWhitespace sniff
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.PostStatementComment now ignores comments inside control structure conditions, such as FOR loops
    - Fixes a conflict between this sniff and the Squiz.ControlStructures.ForLoopDeclaration sniff
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.FunctionCommentThrowTag now has improved support for unknown exception types and namespaces
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.ControlStructures.ForLoopDeclaration has improved whitespace, closure, and empty expression support
    - The SpacingAfterSecondNoThird error code has been removed as part of these fixes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.CSS.ClassDefinitionOpeningBraceSpace now handles comments and indentation correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.CSS.ClassDefinitionClosingBrace now handles comments, indentation, and multiple statements on the same line correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.CSS.Opacity now handles comments correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.CSS.SemicolonSpacing now handles comments and syntax errors correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.NamingConventions.ValidVariableName now supports variables inside anonymous classes correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.LowercasePHPFunctions now handles use statements, namespaces, and comments correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.FunctionSpacing now fixes function spacing correctly when a function is the first content in a file
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.SuperfluousWhitespace no longer throws errors for spacing between functions and properties in anon classes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Zend.Files.ClosingTag no longer adds a semicolon during fixing of a file that only contains a comment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Zend.NamingConventions.ValidVariableName now supports variables inside anonymous classes correctly
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed bug [#2298][sq-2298] : PSR2.Classes.ClassDeclaration allows extended class on new line
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#2337][sq-2337] : Generic.WhiteSpace.ScopeIndent incorrect error when multi-line function call starts on same line as open tag
- Fixed bug [#2348][sq-2348] : Cache not invalidated when changing a ruleset included by another
- Fixed bug [#2376][sq-2376] : Using __halt_compiler() breaks Generic.PHP.ForbiddenFunctions unless it's last in the function list
    - Thanks to [Sijun Zhu][@Billz95] for the patch
- Fixed bug [#2393][sq-2393] : The gitmodified filter will infinitely loop when encountering deleted file paths
    - Thanks to [Lucas Manzke][@lmanzke] for the patch
- Fixed bug [#2396][sq-2396] : Generic.WhiteSpace.ScopeIndent incorrect error when multi-line IF condition mixed with HTML
- Fixed bug [#2431][sq-2431] : Use function/const not tokenized as T_STRING when preceded by comment

[sq-2298]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2298
[sq-2337]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2337
[sq-2348]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2348
[sq-2376]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2376
[sq-2393]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2393
[sq-2396]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2396
[sq-2431]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2431

## [3.4.0] - 2018-12-20

### Deprecated
- The Generic.Formatting.NoSpaceAfterCast sniff has been deprecated and will be removed in version 4
    - The functionality of this sniff is now available in the Generic.Formatting.SpaceAfterCast sniff
        - Include the Generic.Formatting.SpaceAfterCast sniff and set the "spacing" property to "0"
    - As soon as possible, replace all instances of the old sniff code with the new sniff code and property setting
        - The existing sniff will continue to work until version 4 has been released

### Changed
- Rule include patterns in a ruleset.xml file are now evaluated as OR instead of AND
    - Previously, a file had to match every include pattern and no exclude patterns to be included
    - Now, a file must match at least one include pattern and no exclude patterns to be included
    - This is a bug fix as include patterns are already documented to work this way
- New token T_BITWISE_NOT added for the bitwise not operator
    - This token was previously tokenized as T_NONE
    - Any sniffs specifically looking for T_NONE tokens with a tilde as the contents must now also look for T_BITWISE_NOT
    - Sniffs can continue looking for T_NONE as well as T_BITWISE_NOT to support older PHP_CodeSniffer versions
- All types of binary casting are now tokenized as T_BINARY_CAST
    - Previously, the 'b' in 'b"some string with $var"' would be a T_BINARY_CAST, but only when the string contained a var
    - This change ensures the 'b' is always tokenized as T_BINARY_CAST
    - This change also converts '(binary)' from T_STRING_CAST to T_BINARY_CAST
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the help with this patch
- Array properties set inside a ruleset.xml file can now extend a previous value instead of always overwriting it
    - e.g., if you include a ruleset that defines forbidden functions, can you now add to that list instead of having to redefine it
    - To use this feature, add extend="true" to the property tag
        - e.g., property name="forbiddenFunctionNames" type="array" extend="true"
    - Thanks to [Michael Moravec][@Majkl578] for the patch
- If $XDG_CACHE_HOME is set and points to a valid directory, it will be used for caching instead of the system temp directory
- PHPCBF now disables parallel running if you are passing content on STDIN
    - Stops an error from being shown after the fixed output is printed
- The progress report now shows files with tokenizer errors as skipped (S) instead of a warning (W)
    - The tokenizer error is still displayed in reports as normal
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The Squiz standard now ensures there is no space between an increment/decrement operator and its variable
- The File::getMethodProperties() method now includes a has_body array index in the return value
    - FALSE if the method has no body (as with abstract and interface methods) or TRUE otherwise
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- The File::getTokensAsString() method now throws an exception if the $start param is invalid
    - If the $length param is invalid, an empty string will be returned
    - Stops an infinite loop when the function is passed invalid data
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added new Generic.CodeAnalysis.EmptyPHPStatement sniff
    - Warns when it finds empty PHP open/close tag combinations or superfluous semicolons
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added new Generic.Formatting.SpaceBeforeCast sniff
    - Ensures there is exactly 1 space before a type cast, unless the cast statement is indented or multi-line
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added new Generic.VersionControl.GitMergeConflict sniff
    - Detects merge conflict artifacts left in files
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added Generic.WhiteSpace.IncrementDecrementSpacing sniff
    - Ensures there is no space between the operator and the variable it applies to
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added PSR12.Functions.NullableTypeDeclaration sniff
    - Ensures there is no space after the question mark in a nullable type declaration
    - Thanks to [Timo Schinkel][@timoschinkel] for the contribution
- A number of sniffs have improved support for methods in anonymous classes
    - These sniffs would often throw the same error twice for functions in nested classes
    - Error messages have also been changed to be less confusing
    - The full list of affected sniffs is:
        - Generic.NamingConventions.CamelCapsFunctionName
        - PEAR.NamingConventions.ValidFunctionName
        - PSR1.Methods.CamelCapsMethodName
        - PSR2.Methods.MethodDeclaration
        - Squiz.Scope.MethodScope
        - Squiz.Scope.StaticThisUsage
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.CodeAnalysis.UnusedFunctionParameter now only skips functions with empty bodies when the class implements an interface
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.CodeAnalysis.UnusedFunctionParameter now has additional error codes to indicate where unused params were found
    - The new error code prefixes are:
        - FoundInExtendedClass: when the class extends another
        - FoundInImplementedInterface: when the class implements an interface
        - Found: used in all other cases, including closures
    - The new error code suffixes are:
        - BeforeLastUsed: the unused param was positioned before the last used param in the function signature
        - AfterLastUsed: the unused param was positioned after the last used param in the function signature
    - This makes the new error code list for this sniff:
        - Found
        - FoundBeforeLastUsed
        - FoundAfterLastUsed
        - FoundInExtendedClass
        - FoundInExtendedClassBeforeLastUsed
        - FoundInExtendedClassAfterLastUsed
        - FoundInImplementedInterface
        - FoundInImplementedInterfaceBeforeLastUsed
        - FoundInImplementedInterfaceAfterLastUsed
    - These errors code make it easier for specific cases to be ignored or promoted using a ruleset.xml file
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Generic.Classes.DuplicateClassName now inspects traits for duplicate names as well as classes and interfaces
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Generic.Files.InlineHTML now ignores a BOM at the start of the file
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Generic.PHP.CharacterBeforePHPOpeningTag now ignores a BOM at the start of the file
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Generic.Formatting.SpaceAfterCast now has a setting to specify how many spaces are required after a type cast
    - Default remains 1
    - Override the "spacing" setting in a ruleset.xml file to change
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Formatting.SpaceAfterCast now has a setting to ignore newline characters after a type cast
    - Default remains FALSE, so newlines are not allowed
    - Override the "ignoreNewlines" setting in a ruleset.xml file to change
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Formatting.SpaceAfterNot now has a setting to specify how many spaces are required after a NOT operator
    - Default remains 1
    - Override the "spacing" setting in a ruleset.xml file to change
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Formatting.SpaceAfterNot now has a setting to ignore newline characters after the NOT operator
    - Default remains FALSE, so newlines are not allowed
    - Override the "ignoreNewlines" setting in a ruleset.xml file to change
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PEAR.Functions.FunctionDeclaration now checks spacing before the opening parenthesis of functions with no body
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- PEAR.Functions.FunctionDeclaration now enforces no space before the semicolon in functions with no body
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- PSR2.Classes.PropertyDeclaration now checks the order of property modifier keywords
    - This is a rule that is documented in PSR-2 but was not enforced by the included PSR2 standard until now
    - This sniff is also able to fix the order of the modifier keywords if they are incorrect
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PSR2.Methods.MethodDeclaration now checks method declarations inside traits
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Squiz.Commenting.InlineComment now has better detection of comment block boundaries
- Squiz.Classes.ClassFileName now checks that a trait name matches the filename
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Squiz.Classes.SelfMemberReference now supports scoped declarations and anonymous classes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Classes.SelfMemberReference now fixes multiple errors at once, increasing fixer performance
    - Thanks to [Gabriel Ostrolucký][@ostrolucky] for the patch
- Squiz.Functions.LowercaseFunctionKeywords now checks abstract and final prefixes, and auto-fixes errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Objects.ObjectMemberComma.Missing has been renamed to Squiz.Objects.ObjectMemberComma.Found
    - The error is thrown when the comma is found but not required, so the error code was incorrect
    - If you are referencing the old error code in a ruleset XML file, please use the new code instead
    - If you wish to maintain backwards compatibility, you can provide rules for both the old and new codes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.ObjectOperatorSpacing is now more tolerant of parse errors
- Squiz.WhiteSpace.ObjectOperatorSpacing now fixes errors more efficiently
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed bug [#2109][sq-2109] : Generic.Functions.CallTimePassByReference false positive for bitwise and used in function argument
- Fixed bug [#2165][sq-2165] : Conflict between Squiz.Arrays.ArrayDeclaration and ScopeIndent sniffs when heredoc used in array
- Fixed bug [#2167][sq-2167] : Generic.WhiteSpace.ScopeIndent shows invalid error when scope opener indented inside inline HTML
- Fixed bug [#2178][sq-2178] : Generic.NamingConventions.ConstructorName matches methods in anon classes with same name as containing class
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2190][sq-2190] : PEAR.Functions.FunctionCallSignature incorrect error when encountering trailing PHPCS annotation
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2194][sq-2194] : Generic.Whitespace.LanguageConstructSpacing should not be checking namespace operators
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2202][sq-2202] : Squiz.WhiteSpace.OperatorSpacing throws error for negative index when using curly braces for string access
    - Same issue fixed in Squiz.Formatting.OperatorBracket
    - Thanks to [Andreas Buchenrieder][@anbuc] for the patch
- Fixed bug [#2210][sq-2210] : Generic.NamingConventions.CamelCapsFunctionName not ignoring SoapClient __getCookies() method
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2211][sq-2211] : PSR2.Methods.MethodDeclaration gets confused over comments between modifier keywords
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2212][sq-2212] : FUNCTION and CONST in use groups being tokenized as T_FUNCTION and T_CONST
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Fixed bug [#2214][sq-2214] : File::getMemberProperties() is recognizing method params as properties
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2236][sq-2236] : Memory info measurement unit is Mb but probably should be MB
- Fixed bug [#2246][sq-2246] : CSS tokenizer does not tokenize class names correctly when they contain the string NEW
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2278][sq-2278] : Squiz.Operators.ComparisonOperatorUsage false positive when inline IF contained in parentheses
    - Thanks to [Arnout Boks][@aboks] for the patch
- Fixed bug [#2284][sq-2284] : Squiz.Functions.FunctionDeclarationArgumentSpacing removing type hint during fixing
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#2297][sq-2297] : Anonymous class not tokenized correctly when used as argument to another anon class
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch

[sq-2109]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2109
[sq-2165]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2165
[sq-2167]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2167
[sq-2178]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2178
[sq-2190]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2190
[sq-2194]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2194
[sq-2202]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2202
[sq-2210]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2210
[sq-2211]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2211
[sq-2212]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2212
[sq-2214]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2214
[sq-2236]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2236
[sq-2246]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2246
[sq-2278]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2278
[sq-2284]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2284
[sq-2297]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2297

## [3.3.2] - 2018-09-24

### Changed
- Fixed a problem where the report cache was not being cleared when the sniffs inside a standard were updated
- The info report (--report=info) now has improved formatting for metrics that span multiple lines
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The unit test runner now skips .bak files when looking for test cases
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The Squiz standard now ensures underscores are not used to indicate visibility of private members vars and methods
    - Previously, this standard enforced the use of underscores
- Generic.PHP.NoSilencedErrors error messages now contain a code snippet to show the context of the error
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Arrays.ArrayDeclaration no longer reports errors for a comma on a line new after a here/nowdoc
    - Also stops a parse error being generated when auto-fixing
    - The SpaceBeforeComma error message has been changed to only have one data value instead of two
- Squiz.Commenting.FunctionComment no longer errors when trying to fix indents of multi-line param comments
- Squiz.Formatting.OperatorBracket now correctly fixes statements that contain strings
- Squiz.PHP.CommentedOutCode now ignores more @-style annotations and includes better comment block detection
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed a problem where referencing a relative file path in a ruleset XML file could add unnecessary sniff exclusions
    - This didn't actually exclude anything, but caused verbose output to list strange exclusion rules
- Fixed bug [#2110][sq-2110] : Squiz.WhiteSpace.FunctionSpacing is removing indents from the start of functions when fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2115][sq-2115] : Squiz.Commenting.VariableComment not checking var types when the @var line contains a comment
- Fixed bug [#2120][sq-2120] : Tokenizer fails to match T_INLINE_ELSE when used after function call containing closure
- Fixed bug [#2121][sq-2121] : Squiz.PHP.DisallowMultipleAssignments false positive in while loop conditions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2127][sq-2127] : File::findExtendedClassName() doesn't support nested classes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2138][sq-2138] : Tokenizer detects wrong token for PHP ::class feature with spaces
- Fixed bug [#2143][sq-2143] : PSR2.Namespaces.UseDeclaration does not properly fix "use function" and "use const" statements
    - Thanks to [Chris Wilkinson][@thewilkybarkid] for the patch
- Fixed bug [#2144][sq-2144] : Squiz.Arrays.ArrayDeclaration does incorrect align calculation in array with cyrillic keys
- Fixed bug [#2146][sq-2146] : Zend.Files.ClosingTag removes closing tag from end of file without inserting a semicolon
- Fixed bug [#2151][sq-2151] : XML schema not updated with the new array property syntax

[sq-2110]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2110
[sq-2115]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2115
[sq-2120]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2120
[sq-2121]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2121
[sq-2127]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2127
[sq-2138]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2138
[sq-2143]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2143
[sq-2144]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2144
[sq-2146]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2146
[sq-2151]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2151

## [3.3.1] - 2018-07-27

### Removed
- Support for HHVM has been dropped due to recent unfixed bugs and HHVM refocus on Hack only
    - Thanks to [Walt Sorensen][@photodude] and [Juliette Reinders Folmer][@jrfnl] for helping to remove all HHVM exceptions from the core

### Changed
- The full report (the default report) now has improved word wrapping for multi-line messages and sniff codes
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The summary report now sorts files based on their directory location instead of just a basic string sort
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The source report now orders error codes by name when they have the same number of errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The junit report no longer generates validation errors with the Jenkins xUnit plugin
    - Thanks to [Nikolay Geo][@nicholascus] for the patch
- Generic.Commenting.DocComment no longer generates the SpacingBeforeTags error if tags are the first content in the docblock
    - The sniff will still generate a MissingShort error if there is no short comment
    - This allows the MissingShort error to be suppressed in a ruleset to make short descriptions optional
- Generic.Functions.FunctionCallArgumentSpacing now properly fixes multi-line function calls with leading commas
    - Previously, newlines between function arguments would be removed
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.Syntax will now use PHP_BINARY instead of trying to discover the executable path
    - This ensures that the sniff will always syntax check files using the PHP version that PHPCS is running under
    - Setting the `php_path` config var will still override this value as normal
    - Thanks to [Willem Stuursma-Ruwen][@willemstuursma] for the patch
- PSR2.Namespaces.UseDeclaration now supports commas at the end of group use declarations
    - Also improves checking and fixing for use statements containing parse errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Arrays.ArrayDeclaration no longer removes the array opening brace while fixing
    - This could occur when the opening brace was on a new line and the first array key directly followed
    - This change also stops the KeyNotAligned error message being incorrectly reported in these cases
- Squiz.Arrays.ArrayDeclaration no longer tries to change multi-line arrays to single line when they contain comments
    - Fixes a conflict between this sniff and some indentation sniffs
- Squiz.Classes.ClassDeclaration no longer enforces spacing rules when a class is followed by a function
    - Fixes a conflict between this sniff and the Squiz.WhiteSpace.FunctionSpacing sniff
- The Squiz.Classes.ValidClassName.NotCamelCaps message now references PascalCase instead of CamelCase
    - The "CamelCase class name" metric produced by the sniff has been changed to "PascalCase class name"
    - This reflects the fact that the class name check is actually a Pascal Case check and not really Camel Case
    - Thanks to [Tom H Anderson][@TomHAnderson] for the patch
- Squiz.Commenting.InlineComment no longer enforces spacing rules when an inline comment is followed by a docblock
    - Fixes a conflict between this sniff and the Squiz.WhiteSpace.FunctionSpacing sniff
- Squiz.WhiteSpace.OperatorSpacing no longer tries to fix operator spacing if the next content is a comment on a new line
    - Fixes a conflict between this sniff and the Squiz.Commenting.PostStatementComment sniff
    - Also stops PHPCS annotations from being moved to a different line, potentially changing their meaning
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.FunctionSpacing no longer checks spacing of functions at the top of an embedded PHP block
    - Fixes a conflict between this sniff and the Squiz.PHP.EmbeddedPHP sniff
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.MemberVarSpacing no longer checks spacing before member vars that come directly after methods
    - Fixes a conflict between this sniff and the Squiz.WhiteSpace.FunctionSpacing sniff
- Squiz.WhiteSpace.SuperfluousWhitespace now recognizes unicode whitespace at the start and end of a file
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed bug [#2029][sq-2029] : Squiz.Scope.MemberVarScope throws fatal error when a property is found in an interface
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2047][sq-2047] : PSR12.Classes.ClassInstantiation false positive when instantiating class from array index
- Fixed bug [#2048][sq-2048] : GenericFormatting.MultipleStatementAlignment false positive when assigning values inside an array
- Fixed bug [#2053][sq-2053] : PSR12.Classes.ClassInstantiation incorrectly fix when using member vars and some variable formats
- Fixed bug [#2065][sq-2065] : Generic.ControlStructures.InlineControlStructure fixing fails when inline control structure contains closure
- Fixed bug [#2072][sq-2072] : Squiz.Arrays.ArrayDeclaration throws NoComma error when array value is a shorthand IF statement
- Fixed bug [#2082][sq-2082] : File with "defined() or define()" syntax triggers PSR1.Files.SideEffects.FoundWithSymbols
- Fixed bug [#2095][sq-2095] : PSR2.Namespaces.NamespaceDeclaration does not handle namespaces defined over multiple lines

[sq-2029]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2029
[sq-2047]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2047
[sq-2048]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2048
[sq-2053]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2053
[sq-2065]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2065
[sq-2072]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2072
[sq-2082]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2082
[sq-2095]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2095

## [3.3.0] - 2018-06-07

### Deprecated
- The Squiz.WhiteSpace.LanguageConstructSpacing sniff has been deprecated and will be removed in version 4
    - The sniff has been moved to the Generic standard, with a new code of Generic.WhiteSpace.LanguageConstructSpacing
    - As soon as possible, replace all instances of the old sniff code with the new sniff code in your ruleset.xml files
        - The existing Squiz sniff will continue to work until version 4 has been released
    - The new Generic sniff now also checks many more language constructs to enforce additional spacing rules
        - Thanks to [Mponos George][@gmponos] for the contribution
- The current method for setting array properties in ruleset files has been deprecated and will be removed in version 4
    - Currently, setting an array value uses the string syntax "print=>echo,create_function=>null"
    - Now, individual array elements are specified using a new "element" tag with "key" and "value" attributes
        - For example, element key="print" value="echo"
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- The T_ARRAY_HINT token has been deprecated and will be removed in version 4
    - The token was used to ensure array type hints were not tokenized as T_ARRAY, but no other type hints were given a special token
    - Array type hints now use the standard T_STRING token instead
    - Sniffs referencing this token type will continue to run without error until version 4, but will not find any T_ARRAY_HINT tokens
- The T_RETURN_TYPE token has been deprecated and will be removed in version 4
    - The token was used to ensure array/self/parent/callable return types were tokenized consistently
    - For namespaced return types, only the last part of the string (the class name) was tokenized as T_RETURN_TYPE
    - This was not consistent and so return types are now left using their original token types so they are not skipped by sniffs
        - The exception are array return types, which are tokenized as T_STRING instead of T_ARRAY, as they are for type hints
    - Sniffs referencing this token type will continue to run without error until version 4, but will not find any T_RETUTN_TYPE tokens
    - To get the return type of a function, use the File::getMethodProperties() method, which now contains a "return_type" array index
        - This contains the return type of the function or closer, or a blank string if not specified
        - If the return type is nullable, the return type will contain the leading ?
            - A nullable_return_type array index in the return value will also be set to true
        - If the return type contains namespace information, it will be cleaned of whitespace and comments
            - To access the original return value string, use the main tokens array

### Added
- This release contains an incomplete version of the PSR-12 coding standard
    - Errors found using this standard should be valid, but it will miss a lot of violations until it is complete
    - If you'd like to test and help, you can use the standard by running PHPCS with --standard=PSR12

### Changed
- Config values set using --runtime-set now override any config values set in rulesets or the CodeSniffer.conf file
- You can now apply include-pattern rules to individual message codes in a ruleset like you can with exclude-pattern rules
    - Previously, include-pattern rules only applied to entire sniffs
    - If a message code has both include and exclude patterns, the exclude patterns will be ignored
- Using PHPCS annotations to selectively re-enable sniffs is now more flexible
    - Previously, you could only re-enable a sniff/category/standard using the exact same code that was disabled
    - Now, you can disable a standard and only re-enable a specific category or sniff
    - Or, you can disable a specific sniff and have it re-enable when you re-enable the category or standard
- The value of array sniff properties can now be set using phpcs:set annotations
    - e.g., phpcs:set Standard.Category.SniffName property[] key=>value,key2=>value2
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- PHPCS annotations now remain as T_PHPCS_* tokens instead of reverting to comment tokens when --ignore-annotations is used
    - This stops sniffs (especially commenting sniffs) from generating a large number of false errors when ignoring
    - Any custom sniffs that are using the T_PHPCS_* tokens to detect annotations may need to be changed to ignore them
        - Check $phpcsFile->config->annotations to see if annotations are enabled and ignore when false
- You can now use fully or partially qualified class names for custom reports instead of absolute file paths
    - To support this, you must specify an autoload file in your ruleset.xml file and use it to register an autoloader
    - Your autoloader will need to load your custom report class when requested
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The JSON report format now does escaping in error source codes as well as error messages
    - Thanks to [Martin Vasel][@marvasDE] for the patch
- Invalid installed_paths values are now ignored instead of causing a fatal error
- Improved testability of custom rulesets by allowing the installed standards to be overridden
    - Thanks to [Timo Schinkel][@timoschinkel] for the patch
- The key used for caching PHPCS runs now includes all set config values
    - This fixes a problem where changing config values (e.g., via --runtime-set) used an incorrect cache file
- The "Function opening brace placement" metric has been separated into function and closure metrics in the info report
    - Closures are no longer included in the "Function opening brace placement" metric
    - A new "Closure opening brace placement" metric now shows information for closures
- Multi-line T_YIELD_FROM statements are now replicated properly for older PHP versions
- The PSR2 standard no longer produces 2 error messages when the AS keyword in a foreach loop is not lowercase
- Specifying a path to a non-existent dir when using the `--report-[reportType]=/path/to/report` CLI option no longer throws an exception
    - This now prints a readable error message, as it does when using `--report-file`
- The File::getMethodParamaters() method now includes a type_hint_token array index in the return value
    - Provides the position in the token stack of the first token in the type hint
- The File::getMethodProperties() method now includes a return_type_token array index in the return value
    - Provides the position in the token stack of the first token in the return type
- The File::getTokensAsString() method can now optionally return original (non tab-replaced) content
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Removed Squiz.PHP.DisallowObEndFlush from the Squiz standard
    - If you use this sniff and want to continue banning ob_end_flush(), use Generic.PHP.ForbiddenFunctions instead
    - You will need to set the forbiddenFunctions property in your ruleset.xml file
- Removed Squiz.PHP.ForbiddenFunctions from the Squiz standard
    - Replaced by using the forbiddenFunctions property of Generic.PHP.ForbiddenFunctions in the Squiz ruleset.xml
    - Functionality of the Squiz standard remains the same, but the error codes are now different
    - Previously, Squiz.PHP.ForbiddenFunctions.Found and Squiz.PHP.ForbiddenFunctions.FoundWithAlternative
    - Now, Generic.PHP.ForbiddenFunctions.Found and Generic.PHP.ForbiddenFunctions.FoundWithAlternative
- Added new Generic.PHP.LowerCaseType sniff
    - Ensures PHP types used for type hints, return types, and type casting are lowercase
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added new Generic.WhiteSpace.ArbitraryParenthesesSpacing sniff
    - Generates an error for whitespace inside parenthesis that don't belong to a function call/declaration or control structure
    - Generates a warning for any empty parenthesis found
    - Allows the required spacing to be set using the spacing sniff property (default is 0)
    - Allows newlines to be used by setting the ignoreNewlines sniff property (default is false)
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added new PSR12.Classes.ClassInstantiation sniff
    - Ensures parenthesis are used when instantiating a new class
- Added new PSR12.Keywords.ShortFormTypeKeywords sniff
    - Ensures the short form of PHP types is used when type casting
- Added new PSR12.Namespaces.CompundNamespaceDepth sniff
    - Ensures compound namespace use statements have a max depth of 2 levels
    - The max depth can be changed by setting the 'maxDepth' sniff property in a ruleset.xml file
- Added new PSR12.Operators.OperatorSpacing sniff
    - Ensures operators are preceded and followed by at least 1 space
- Improved core support for grouped property declarations
    - Also improves support in Squiz.WhiteSpace.ScopeKeywordSpacing and Squiz.WhiteSpace.MemberVarSpacing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Commenting.DocComment now produces a NonParamGroup error when tags are mixed in with the @param tag group
    - It would previously throw either a NonParamGroup or ParamGroup error depending on the order of tags
    - This change allows the NonParamGroup error to be suppressed in a ruleset to allow the @param group to contain other tags
    - Thanks to [Phil Davis][@phil-davis] for the patch
- Generic.Commenting.DocComment now continues checks param tags even if the doc comment short description is missing
    - This change allows the MissingShort error to be suppressed in a ruleset without all other errors being suppressed as well
    - Thanks to [Phil Davis][@phil-davis] for the patch
- Generic.CodeAnalysis.AssignmentInCondition now reports a different error code for assignments found in WHILE conditions
    - The return value of a function call is often assigned in a WHILE condition, so this change makes it easier to exclude these cases
    - The new code for this error message is Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
    - The error code for all other cases remains as Generic.CodeAnalysis.AssignmentInCondition.Found
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Functions.OpeningFunctionBraceBsdAllman now longer leaves trailing whitespace when moving the opening brace during fixing
    - Also applies to fixes made by PEAR.Functions.FunctionDeclaration and Squiz.Functions.MultiLineFunctionDeclaration
- Generic.WhiteSpace.ScopeIndent now does a better job of fixing the indent of multi-line comments
- Generic.WhiteSpace.ScopeIndent now does a better job of fixing the indent of PHP open and close tags
- PEAR.Commenting.FunctionComment now report a different error code for param comment lines with too much padding
    - Previously, any lines of a param comment that don't start at the exact comment position got the same error code
    - Now, only comment lines with too little padding use ParamCommentAlignment as they are clearly mistakes
    - Comment lines with too much padding may be using precision alignment as now use ParamCommentAlignmentExceeded
    - This allows for excessive padding to be excluded from a ruleset while continuing to enforce a minimum padding
- PEAR.WhiteSpace.ObjectOperatorIndent now checks the indent of more chained operators
    - Previously, it only checked chains beginning with a variable
    - Now, it checks chains beginning with function calls, static class names, etc
- Squiz.Arrays.ArrayDeclaration now continues checking array formatting even if the key indent is not correct
    - Allows for using different array indent rules while still checking/fixing double arrow and value alignment
- Squiz.Commenting.BlockComment has improved support for tab-indented comments
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.BlockComment auto fixing no longer breaks when two block comments follow each other
    - Also stopped single-line block comments from being auto fixed when they are embedded in other code
    - Also fixed as issue found when PHPCS annotations were used inside a block comment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.BlockComment.LastLineIndent is now able to be fixed with phpcbf
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.BlockComment now aligns star-prefixed lines under the opening tag while fixing, instead of indenting them
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.FunctionComment.IncorrectTypeHint message no longer contains cut-off suggested type hints
- Squiz.Commenting.InlineComment now uses a new error code for inline comments at the end of a function
    - Previously, all inline comments followed by a blank line threw a Squiz.Commenting.InlineComment.SpacingAfter error
    - Now, inline comments at the end of a function will instead throw Squiz.Commenting.InlineComment.SpacingAfterAtFunctionEnd
    - If you previously excluded SpacingAfter, add an exclusion for SpacingAfterAtFunctionEnd to your ruleset as well
    - If you previously only included SpacingAfter, consider including SpacingAfterAtFunctionEnd as well
    - The Squiz standard now excludes SpacingAfterAtFunctionEnd as the blank line is checked elsewhere
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.ControlStructures.ControlSignature now errors when a comment follows the closing brace of an earlier body
    - Applies to catch, finally, else, elseif, and do/while structures
    - The included PSR2 standard now enforces this rule
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Formatting.OperatorBracket.MissingBrackets message has been changed to remove the word "arithmetic"
    - The sniff checks more than just arithmetic operators, so the message is now clearer
- Sniffs.Operators.ComparisonOperatorUsage now detects more cases of implicit true comparisons
    - It could previously be confused by comparisons used as function arguments
- Squiz.PHP.CommentedOutCode now ignores simple @-style annotation comments so they are not flagged as commented out code
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.CommentedOutCode now ignores a greater number of short comments so they are not flagged as commented out code
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.DisallowComparisonAssignment no longer errors when using the null coalescing operator
    - Given this operator is used almost exclusively to assign values, it didn't make sense to generate an error
- Squiz.WhiteSpacing.FunctionSpacing now has a property to specify how many blank lines should be before the first class method
    - Only applies when a method is the first code block in a class (i.e., there are no member vars before it)
    - Override the 'spacingBeforeFirst' property in a ruleset.xml file to change
    - If not set, the sniff will use whatever value is set for the existing 'spacing' property
- Squiz.WhiteSpacing.FunctionSpacing now has a property to specify how many blank lines should be after the last class method
    - Only applies when a method is the last code block in a class (i.e., there are no member vars after it)
    - Override the 'spacingAfterLast' property in a ruleset.xml file to change
    - If not set, the sniff will use whatever value is set for the existing 'spacing' property

### Fixed
- Fixed bug [#1863][sq-1863] : File::findEndOfStatement() not working when passed a scope opener
- Fixed bug [#1876][sq-1876] : PSR2.Namespaces.UseDeclaration not giving error for use statements before the namespace declaration
    - Adds a new PSR2.Namespaces.UseDeclaration.UseBeforeNamespace error message
- Fixed bug [#1881][sq-1881] : Generic.Arrays.ArrayIndent is indenting sub-arrays incorrectly when comma not used after the last value
- Fixed bug [#1882][sq-1882] : Conditional with missing braces confused by indirect variables
- Fixed bug [#1915][sq-1915] : JS tokenizer fails to tokenize regular expression proceeded by boolean not operator
- Fixed bug [#1920][sq-1920] : Directory exclude pattern improperly excludes files with names that start the same
    - Thanks to [Jeff Puckett][@jpuck] for the patch
- Fixed bug [#1922][sq-1922] : Equal sign alignment check broken when list syntax used before assignment operator
- Fixed bug [#1925][sq-1925] : Generic.Formatting.MultipleStatementAlignment skipping assignments within closures
- Fixed bug [#1931][sq-1931] : Generic opening brace placement sniffs do not correctly support function return types
- Fixed bug [#1932][sq-1932] : Generic.ControlStructures.InlineControlStructure fixer moves new PHPCS annotations
- Fixed bug [#1938][sq-1938] : Generic opening brace placement sniffs incorrectly move PHPCS annotations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1939][sq-1939] : phpcs:set annotations do not cause the line they are on to be ignored
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1949][sq-1949] : Squiz.PHP.DisallowMultipleAssignments false positive when using namespaces with static assignments
- Fixed bug [#1959][sq-1959] : SquizMultiLineFunctionDeclaration error when param has trailing comment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1963][sq-1963] : Squiz.Scope.MemberVarScope does not work for multiline member declaration
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1971][sq-1971] : Short array list syntax not correctly tokenized if short array is the first content in a file
- Fixed bug [#1979][sq-1979] : Tokenizer does not change heredoc to nowdoc token if the start tag contains spaces
- Fixed bug [#1982][sq-1982] : Squiz.Arrays.ArrayDeclaration fixer sometimes puts a comma in front of the last array value
- Fixed bug [#1993][sq-1993] : PSR1/PSR2 not reporting or fixing short open tags
- Fixed bug [#1996][sq-1996] : Custom report paths don't work on case-sensitive filesystems
- Fixed bug [#2006][sq-2006] : Squiz.Functions.FunctionDeclarationArgumentSpacing fixer removes comment between parens when no args
    - The SpacingAfterOpenHint error message has been removed
        - It is replaced by the existing SpacingAfterOpen message
    - The error message format for the SpacingAfterOpen and SpacingBeforeClose messages has been changed
        - These used to contain 3 pieces of data, but now only contain 2
    - If you have customised the error messages of this sniff, please review your ruleset after upgrading
- Fixed bug [#2018][sq-2018] : Generic.Formatting.MultipleStatementAlignment does see PHP close tag as end of statement block
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#2027][sq-2027] : PEAR.NamingConventions.ValidFunctionName error when function name includes double underscore
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-1863]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1863
[sq-1876]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1876
[sq-1881]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1881
[sq-1882]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1882
[sq-1915]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1915
[sq-1920]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1920
[sq-1922]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1922
[sq-1925]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1925
[sq-1931]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1931
[sq-1932]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1932
[sq-1938]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1938
[sq-1939]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1939
[sq-1949]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1949
[sq-1959]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1959
[sq-1963]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1963
[sq-1971]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1971
[sq-1979]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1979
[sq-1982]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1982
[sq-1993]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1993
[sq-1996]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1996
[sq-2006]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2006
[sq-2018]: https://github.com/squizlabs/PHP_CodeSniffer/pull/2018
[sq-2027]: https://github.com/squizlabs/PHP_CodeSniffer/issues/2027

## [3.2.3] - 2018-02-21

### Changed
- The new phpcs: comment syntax can now be prefixed with an at symbol ( @phpcs: )
    - This restores the behaviour of the previous syntax where these comments are ignored by doc generators
- The current PHP version ID is now used to generate cache files
    - This ensures that only cache files generated by the current PHP version are selected
    - This change fixes caching issues when using sniffs that produce errors based on the current PHP version
- A new Tokens::$phpcsCommentTokens array is now available for sniff developers to detect phpcs: comment syntax
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The PEAR.Commenting.FunctionComment.Missing error message now includes the name of the function
    - Thanks to [Yorman Arias][@cixtor] for the patch
- The PEAR.Commenting.ClassComment.Missing and Squiz.Commenting.ClassComment.Missing error messages now include the name of the class
    - Thanks to [Yorman Arias][@cixtor] for the patch
- PEAR.Functions.FunctionCallSignature now only forces alignment at a specific tab stop while fixing
    - It was enforcing this during checking, but this meant invalid errors if the OpeningIndent message was being muted
    - This fixes incorrect errors when using the PSR2 standard with some code blocks
- Generic.Files.LineLength now ignores lines that only contain phpcs: annotation comments
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.Formatting.MultipleStatementAlignment now skips over arrays containing comments
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.Syntax now forces display_errors to ON when linting
    - Thanks to [Raúl Arellano][@raul338] for the patch
- PSR2.Namespaces.UseDeclaration has improved syntax error handling and closure detection
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.CommentedOutCode now has improved comment block detection for improved accuracy
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.NonExecutableCode could fatal error while fixing file with syntax error
- Squiz.PHP.NonExecutableCode now detects unreachable code after a goto statement
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.LanguageConstructSpacing has improved syntax error handling while fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Improved phpcs: annotation syntax handling for a number of sniffs
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Improved auto-fixing of files with incomplete comment blocks for various commenting sniffs
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed test suite compatibility with PHPUnit 7
- Fixed bug [#1793][sq-1793] : PSR2 forcing exact indent for function call opening statements
- Fixed bug [#1803][sq-1803] : Squiz.WhiteSpace.ScopeKeywordSpacing removes member var name while fixing if no space after scope keyword
- Fixed bug [#1817][sq-1817] : Blank line not enforced after control structure if comment on same line as closing brace
- Fixed bug [#1827][sq-1827] : A phpcs:enable comment is not tokenized correctly if it is outside a phpcs:disable block
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1828][sq-1828] : Squiz.WhiteSpace.SuperfluousWhiteSpace ignoreBlankLines property ignores whitespace after single line comments
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1840][sq-1840] : When a comment has too many asterisks, phpcbf gives FAILED TO FIX error
- Fixed bug [#1867][sq-1867] : Can't use phpcs:ignore where the next line is HTML
- Fixed bug [#1870][sq-1870] : Invalid warning in multiple assignments alignment with closure or anon class
- Fixed bug [#1890][sq-1890] : Incorrect Squiz.WhiteSpace.ControlStructureSpacing.NoLineAfterClose error between catch and finally statements
- Fixed bug [#1891][sq-1891] : Comment on last USE statement causes false positive for PSR2.Namespaces.UseDeclaration.SpaceAfterLastUse
    - Thanks to [Matt Coleman][@iammattcoleman], [Daniel Hensby][@dhensby], and [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1901][sq-1901] : Fixed PHPCS annotations in multi-line tab-indented comments + not ignoring whole line for phpcs:set
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-1793]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1793
[sq-1803]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1803
[sq-1817]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1817
[sq-1827]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1827
[sq-1828]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1828
[sq-1840]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1840
[sq-1867]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1867
[sq-1870]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1870
[sq-1890]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1890
[sq-1891]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1891
[sq-1901]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1901

## [3.2.2] - 2017-12-20

### Changed
- Disabled STDIN detection on Windows
    - This fixes a problem with IDE plugins (e.g., PHPStorm) hanging on Windows

## [3.2.1] - 2017-12-18

### Changed
- Empty diffs are no longer followed by a newline character (request [#1781][sq-1781])
- Generic.Functions.OpeningFunctionBraceKernighanRitchie no longer complains when the open brace is followed by a close tag
    - This makes the sniff more useful when used in templates
    - Thanks to [Joseph Zidell][@josephzidell] for the patch

### Fixed
- Fixed problems with some scripts and plugins waiting for STDIN
    - This was a notable problem with IDE plugins (e.g., PHPStorm) and build systems
- Fixed bug [#1782][sq-1782] : Incorrect detection of operator in ternary + anonymous function

[sq-1781]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1781
[sq-1782]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1782

## [3.2.0] - 2017-12-13

### Deprecated
- This release deprecates the @codingStandards comment syntax used for sending commands to PHP_CodeSniffer
    - The existing syntax will continue to work in all version 3 releases, but will be removed in version 4
    - The comment formats have been replaced by a shorter syntax:
        - @codingStandardsIgnoreFile becomes phpcs:ignoreFile
        - @codingStandardsIgnoreStart becomes phpcs:disable
        - @codingStandardsIgnoreEnd becomes phpcs:enable
        - @codingStandardsIgnoreLine becomes phpcs:ignore
        - @codingStandardsChangeSetting becomes phpcs:set
    - The new syntax allows for additional developer comments to be added after a -- separator
        - This is useful for describing why a code block is being ignored, or why a setting is being changed
        - E.g., phpcs:disable -- This code block must be left as-is.
    - Comments using the new syntax are assigned new comment token types to allow them to be detected:
        - phpcs:ignoreFile has the token T_PHPCS_IGNORE_FILE
        - phpcs:disable has the token T_PHPCS_DISABLE
        - phpcs:enable has the token T_PHPCS_ENABLE
        - phpcs:ignore has the token T_PHPCS_IGNORE
        - phpcs:set has the token T_PHPCS_SET

### Changed
- The phpcs:disable and phpcs:ignore comments can now selectively ignore specific sniffs (request [#604][sq-604])
    - E.g., phpcs:disable Generic.Commenting.Todo.Found for a specific message
    - E.g., phpcs:disable Generic.Commenting.Todo for a whole sniff
    - E.g., phpcs:disable Generic.Commenting for a whole category of sniffs
    - E.g., phpcs:disable Generic for a whole standard
    - Multiple sniff codes can be specified by comma separating them
        - E.g., phpcs:disable Generic.Commenting.Todo,PSR1.Files
- @codingStandardsIgnoreLine comments now only ignore the following line if they are on a line by themselves
    - If they are at the end of an existing line, they will only ignore the line they are on
    - Stops some lines from accidentally being ignored
    - Same rule applies for the new phpcs:ignore comment syntax
- PSR1.Files.SideEffects now respects the new phpcs:disable comment syntax
    - The sniff will no longer check any code that is between phpcs:disable and phpcs:enable comments
    - The sniff does not support phpcs:ignore; you must wrap code structures with disable/enable comments
    - Previously, there was no way to have this sniff ignore parts of a file
- Fixed a problem where PHPCS would sometimes hang waiting for STDIN, or read incomplete versions of large files
    - Thanks to [Arne Jørgensen][@arnested] for the patch
- Array properties specified in ruleset files now have their keys and values trimmed
    - This saves having to do this in individual sniffs and stops errors introduced by whitespace in rulesets
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Added phpcs.xsd to allow validation of ruleset XML files
    - Thanks to [Renaat De Muynck][@renaatdemuynck] for the contribution
- File paths specified using --stdin-path can now point to fake file locations (request [#1488][sq-1488])
    - Previously, STDIN files using fake file paths were excluded from checking
- Setting an empty basepath (--basepath=) on the CLI will now clear a basepath set directly in a ruleset
    - Thanks to [Xaver Loppenstedt][@xalopp] for the patch
- Ignore patterns are now checked on symlink target paths instead of symlink source paths
    - Restores previous behaviour of this feature
- Metrics were being double counted when multiple sniffs were recording the same metric
- Added support for bash process substitution
    - Thanks to [Scott Dutton][@exussum12] for the contribution
- Files included in the cache file code hash are now sorted to aid in cache file reuse across servers
- Windows BAT files can now be used outside a PEAR install
    - You must have the path to PHP set in your PATH environment variable
    - Thanks to [Joris Debonnet][@JorisDebonnet] for the patch
- The JS unsigned right shift assignment operator is now properly classified as an assignment operator
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- The AbstractVariableSniff abstract sniff now supports anonymous classes and nested functions
    - Also fixes an issue with Squiz.Scope.MemberVarScope where member vars of anonymous classes were not being checked
- Added AbstractArraySniff to make it easier to create sniffs that check array formatting
    - Allows for checking of single and multi line arrays easily
    - Provides a parsed structure of the array including positions of keys, values, and double arrows
- Added Generic.Arrays.ArrayIndent to enforce a single tab stop indent for array keys in multi-line arrays
    - Also ensures the close brace is on a new line and indented to the same level as the original statement
    - Allows for the indent size to be set using an "indent" property of the sniff
- Added Generic.PHP.DiscourageGoto to warn about the use of the GOTO language construct
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Generic.Debug.ClosureLinter was not running the gjslint command
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Generic.WhiteSpace.DisallowSpaceIndent now fixes space indents in multi-line block comments
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.WhiteSpace.DisallowSpaceIndent now fixes mixed space/tab indents more accurately
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.WhiteSpace.DisallowTabIndent now fixes tab indents in multi-line block comments
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PEAR.Functions.FunctionDeclaration no longer errors when a function declaration is the first content in a JS file
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PEAR.Functions.FunctionCallSignature now requires the function name to be indented to an exact tab stop
    - If the function name is not the start of the statement, the opening statement must be indented correctly instead
    - Added a new fixable error code PEAR.Functions.FunctionCallSignature.OpeningIndent for this error
- Squiz.Functions.FunctionDeclarationArgumentSpacing is no longer confused about comments in function declarations
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.PHP.NonExecutableCode error messages now indicate which line the code block ending is on
    - Makes it easier to identify where the code block exited or returned
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.Commenting.FunctionComment now supports nullable type hints
- Squiz.Commenting.FunctionCommentThrowTag no longer assigns throw tags inside anon classes to the enclosing function
- Squiz.WhiteSpace.SemicolonSpacing now ignores semicolons used for empty statements inside FOR conditions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.ControlStructures.ControlSignature now allows configuring the number of spaces before the colon in alternative syntax
    - Override the 'requiredSpacesBeforeColon' setting in a ruleset.xml file to change
    - Default remains at 1
    - Thanks to [Nikola Kovacs][@nkovacs] for the patch
- The Squiz standard now ensures array keys are indented 4 spaces from the main statement
    - Previously, this standard aligned keys 1 space from the start of the array keyword
- The Squiz standard now ensures array end braces are aligned with the main statement
    - Previously, this standard aligned the close brace with the start of the array keyword
- The standard for PHP_CodeSniffer itself now enforces short array syntax
- The standard for PHP_CodeSniffer itself now uses the Generic.Arrays/ArrayIndent sniff rules
- Improved fixer conflicts and syntax error handling for a number of sniffs
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed bug [#1462][sq-1462] : Error processing cyrillic strings in Tokenizer
- Fixed bug [#1573][sq-1573] : Squiz.WhiteSpace.LanguageConstructSpacing does not properly check for tabs and newlines
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#1590][sq-1590] : InlineControlStructure CBF issue while adding braces to an if that's returning a nested function
- Fixed bug [#1718][sq-1718] : Unclosed strings at EOF sometimes tokenized as T_WHITESPACE by the JS tokenizer
- Fixed bug [#1731][sq-1731] : Directory exclusions do not work as expected when a single file name is passed to phpcs
- Fixed bug [#1737][sq-1737] : Squiz.CSS.EmptyStyleDefinition sees comment as style definition and fails to report error
- Fixed bug [#1746][sq-1746] : Very large reports can sometimes become garbled when using the parallel option
- Fixed bug [#1747][sq-1747] : Squiz.Scope.StaticThisUsage incorrectly looking inside closures
- Fixed bug [#1757][sq-1757] : Unknown type hint "object" in Squiz.Commenting.FunctionComment
- Fixed bug [#1758][sq-1758] : PHPCS gets stuck creating file list when processing circular symlinks
- Fixed bug [#1761][sq-1761] : Generic.WhiteSpace.ScopeIndent error on multi-line function call with static closure argument
- Fixed bug [#1762][sq-1762] : `Generic.WhiteSpace.Disallow[Space/Tab]Indent` not inspecting content before open tag
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1769][sq-1769] : Custom "define" function triggers a warning about declaring new symbols
- Fixed bug [#1776][sq-1776] : Squiz.Scope.StaticThisUsage incorrectly looking inside anon classes
- Fixed bug [#1777][sq-1777] : Generic.WhiteSpace.ScopeIndent incorrect indent errors when self called function proceeded by comment

[sq-604]: https://github.com/squizlabs/PHP_CodeSniffer/issues/604
[sq-1462]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1462
[sq-1488]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1488
[sq-1573]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1573
[sq-1590]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1590
[sq-1718]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1718
[sq-1731]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1731
[sq-1737]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1737
[sq-1746]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1746
[sq-1747]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1747
[sq-1757]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1757
[sq-1758]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1758
[sq-1761]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1761
[sq-1762]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1762
[sq-1769]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1769
[sq-1776]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1776
[sq-1777]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1777

## [3.1.1] - 2017-10-17

### Changed
- Restored preference of non-dist files over dist files for phpcs.xml and phpcs.xml.dist
    - The order that the files are searched is now: .phpcs.xml, phpcs.xml, .phpcs.xml.dist, phpcs.xml.dist
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Progress output now correctly shows skipped files
- Progress output now shows 100% when the file list has finished processing (request [#1697][sq-1697])
- Stopped some IDEs complaining about testing class aliases
    - Thanks to [Vytautas Stankus][@svycka] for the patch
- Squiz.Commenting.InlineComment incorrectly identified comment blocks in some cases, muting some errors
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

### Fixed
- Fixed bug [#1512][sq-1512] : PEAR.Functions.FunctionCallSignature enforces spaces when no arguments if required spaces is not 0
- Fixed bug [#1522][sq-1522] : Squiz Arrays.ArrayDeclaration and Strings.ConcatenationSpacing fixers causing parse errors with here/nowdocs
- Fixed bug [#1570][sq-1570] : Squiz.Arrays.ArrayDeclaration fixer removes comments between array keyword and open parentheses
- Fixed bug [#1604][sq-1604] : File::isReference has problems with some bitwise operators and class property references
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1645][sq-1645] : Squiz.Commenting.InlineComment will fail to fix comments at the end of the file
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1656][sq-1656] : Using the --sniffs argument has a problem with case sensitivity
- Fixed bug [#1657][sq-1657] : Uninitialized string offset: 0 when sniffing CSS
- Fixed bug [#1669][sq-1669] : Temporary expression proceeded by curly brace is detected as function call
- Fixed bug [#1681][sq-1681] : Huge arrays are super slow to scan with Squiz.Arrays.ArrayDeclaration sniff
- Fixed bug [#1694][sq-1694] : Squiz.Arrays.ArrayBracketSpacing is removing some comments during fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1702][sq-1702] : Generic.WhiteSpaceDisallowSpaceIndent fixer bug when line only contains superfluous whitespace

[sq-1512]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1512
[sq-1522]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1522
[sq-1570]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1570
[sq-1604]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1604
[sq-1645]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1645
[sq-1656]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1656
[sq-1657]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1657
[sq-1669]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1669
[sq-1681]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1681
[sq-1694]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1694
[sq-1697]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1697
[sq-1702]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1702

## [3.1.0] - 2017-09-20

### Changed
- This release includes a change to support newer versions of PHPUnit (versions 4, 5, and 6 are now supported)
    - The custom PHP_CodeSniffer test runner now requires a bootstrap file
    - Developers with custom standards using the PHP_CodeSniffer test runner will need to do one of the following:
        - run your unit tests from the PHP_CodeSniffer root dir so the bootstrap file is included
        - specify the PHP_CodeSniffer bootstrap file on the command line: `phpunit --bootstrap=/path/to/phpcs/tests/bootstrap.php`
        - require the PHP_CodeSniffer bootstrap file from your own bootstrap file
    - If you don't run PHP_CodeSniffer unit tests, this change will not affect you
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- A phpcs.xml or phpcs.xml.dist file now takes precedence over the default_standard config setting
    - Thanks to [Björn Fischer][@Fischer-Bjoern] for the patch
- Both phpcs.xml and phpcs.xml.dist files can now be prefixed with a dot (request [#1566][sq-1566])
    - The order that the files are searched is: .phpcs.xml, .phpcs.xml.dist, phpcs.xml, phpcs.xml.dist
- The autoloader will now search for files during unit tests runs from the same locations as during normal phpcs runs
    - Allows for easier unit testing of custom standards that use helper classes or custom namespaces
- Include patterns for sniffs now use OR logic instead of AND logic
    - Previously, a file had to be in each of the include patterns to be processed by a sniff
    - Now, a file has to only be in at least one of the patterns
    - This change reflects the original intention of the feature
- PHPCS will now follow symlinks under the list of checked directories
    - This previously only worked if you specified the path to a symlink on the command line
- Output from --config-show, --config-set, and --config-delete now includes the path to the loaded config file
- PHPCS now cleanly exits if its config file is not readable
    - Previously, a combination of PHP notices and PHPCS errors would be generated
- Comment tokens that start with /** are now always tokenized as docblocks
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- The PHP-supplied T_YIELD and T_YIELD_FROM token have been replicated for older PHP versions
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Added new Generic.CodeAnalysis.AssignmentInCondition sniff to warn about variable assignments inside conditions
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the contribution
- Added Generic.Files.OneObjectStructurePerFile sniff to ensure there is a single class/interface/trait per file
    - Thanks to [Mponos George][@gmponos] for the contribution
- Function call sniffs now check variable function names and self/static object creation
    - Specific sniffs are Generic.Functions.FunctionCallArgumentSpacing, PEAR.Functions.FunctionCallSignature, and PSR2.Methods.FunctionCallSignature
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Generic.Files.LineLength can now be configured to ignore all comment lines, no matter their length
    - Set the ignoreComments property to TRUE (default is FALSE) in your ruleset.xml file to enable this
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Generic.PHP.LowerCaseKeyword now checks self, parent, yield, yield from, and closure (function) keywords
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- PEAR.Functions.FunctionDeclaration now removes a blank line if it creates one by moving the curly brace during fixing
- Squiz.Commenting.FunctionCommentThrowTag now supports PHP 7.1 multi catch exceptions
- Squiz.Formatting.OperatorBracket no longer throws errors for PHP 7.1 multi catch exceptions
- Squiz.Commenting.LongConditionClosingComment now supports finally statements
- Squiz.Formatting.OperatorBracket now correctly fixes pipe separated flags
- Squiz.Formatting.OperatorBracket now correctly fixes statements containing short array syntax
- Squiz.PHP.EmbeddedPhp now properly fixes cases where the only content in an embedded PHP block is a comment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Squiz.WhiteSpace.ControlStructureSpacing now ignores comments when checking blank lines at the top of control structures
- Squiz.WhiteSpace.ObjectOperatorSpacing now detects and fixes spaces around double colons
    - Thanks to [Julius Šmatavičius][@bondas83] for the patch
- Squiz.WhiteSpace.MemberVarSpacing can now be configured to check any number of blank lines between member vars
    - Set the spacing property (default is 1) in your ruleset.xml file to set the spacing
- Squiz.WhiteSpace.MemberVarSpacing can now be configured to check a different number of blank lines before the first member var
    - Set the spacingBeforeFirst property (default is 1) in your ruleset.xml file to set the spacing
- Added a new PHP_CodeSniffer\Util\Tokens::$ooScopeTokens static member var for quickly checking object scope
    - Includes T_CLASS, T_ANON_CLASS, T_INTERFACE, and T_TRAIT
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- PHP_CodeSniffer\Files\File::findExtendedClassName() now supports extended interfaces
    - Thanks to [Martin Hujer][@mhujer] for the patch

### Fixed
- Fixed bug [#1550][sq-1550] : Squiz.Commenting.FunctionComment false positive when function contains closure
- Fixed bug [#1577][sq-1577] : Generic.InlineControlStructureSniff breaks with a comment between body and condition in do while loops
- Fixed bug [#1581][sq-1581] : Sniffs not loaded when one-standard directories are being registered in installed_paths
- Fixed bug [#1591][sq-1591] : Autoloader failing to load arbitrary files when installed_paths only set via a custom ruleset
- Fixed bug [#1605][sq-1605] : Squiz.WhiteSpace.OperatorSpacing false positive on unary minus after comment
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1615][sq-1615] : Uncaught RuntimeException when phpcbf fails to fix files
- Fixed bug [#1637][sq-1637] : Generic.WhiteSpaceScopeIndent closure argument indenting incorrect with multi-line strings
- Fixed bug [#1638][sq-1638] : Squiz.WhiteSpace.ScopeClosingBrace closure argument indenting incorrect with multi-line strings
- Fixed bug [#1640][sq-1640] : Squiz.Strings.DoubleQuoteUsage replaces tabs with spaces when fixing
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch

[sq-1550]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1550
[sq-1566]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1566
[sq-1577]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1577
[sq-1581]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1581
[sq-1591]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1591
[sq-1605]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1605
[sq-1615]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1615
[sq-1637]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1637
[sq-1638]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1638
[sq-1640]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1640

## [3.0.2] - 2017-07-18

### Changed
- The code report now gracefully handles tokenizer exceptions
- The phpcs and phpcbf scripts are now the only places that exit() in the code
    - This allows for easier usage of core PHPCS functions from external scripts
    - If you are calling Runner::runPHPCS() or Runner::runPHPCBF() directly, you will get back the full range of exit codes
    - If not, catch the new DeepExitException to get the error message ($e->getMessage()) and exit code ($e->getCode());
- NOWDOC tokens are now considered conditions, just as HEREDOC tokens are
    - This makes it easier to find the start and end of a NOWDOC from any token within it
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Custom autoloaders are now only included once in case multiple standards are using the same one
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Improved tokenizing of fallthrough CASE and DEFAULT statements that share a closing statement and use curly braces
- Improved the error message when Squiz.ControlStructures.ControlSignature detects a newline after the closing parenthesis

### Fixed
- Fixed a problem where the source report was not printing the correct number of errors found
- Fixed a problem where the --cache=/path/to/cachefile CLI argument was not working
- Fixed bug [#1465][sq-1465] : Generic.WhiteSpace.ScopeIndent reports incorrect errors when indenting double arrows in short arrays
- Fixed bug [#1478][sq-1478] : Indentation in fallthrough CASE that contains a closure
- Fixed bug [#1497][sq-1497] : Fatal error if composer prepend-autoloader is set to false
    - Thanks to [Kunal Mehta][@legoktm] for the patch
- Fixed bug [#1503][sq-1503] : Alternative control structure syntax not always recognized as scoped
- Fixed bug [#1523][sq-1523] : Fatal error when using the --suffix argument
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1526][sq-1526] : Use of basepath setting can stop PHPCBF being able to write fixed files
- Fixed bug [#1530][sq-1530] : Generic.WhiteSpace.ScopeIndent can increase indent too much for lines within code blocks
- Fixed bug [#1547][sq-1547] : Wrong token type for backslash in use function
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#1549][sq-1549] : Squiz.PHP.EmbeddedPhp fixer conflict with // comment before PHP close tag
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1560][sq-1560] : Squiz.Commenting.FunctionComment fatal error when fixing additional param comment lines that have no indent

[sq-1465]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1465
[sq-1478]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1478
[sq-1497]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1497
[sq-1503]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1503
[sq-1523]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1523
[sq-1526]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1526
[sq-1530]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1530
[sq-1547]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1547
[sq-1549]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1549
[sq-1560]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1560

## [3.0.1] - 2017-06-14

### Security
- This release contains a fix for a security advisory related to the improper handling of a shell command
    - A properly crafted filename would allow for arbitrary code execution when using the --filter=gitmodified command line option
    - All version 3 users are encouraged to upgrade to this version, especially if you are checking 3rd-party code
        - e.g., you run PHPCS over libraries that you did not write
        - e.g., you provide a web service that runs PHPCS over user-uploaded files or 3rd-party repositories
        - e.g., you allow external tool paths to be set by user-defined values
    - If you are unable to upgrade but you check 3rd-party code, ensure you are not using the Git modified filter
    - This advisory does not affect PHP_CodeSniffer version 2.
    - Thanks to [Sergei Morozov][@morozov] for the report and patch

### Changed
- Arguments on the command line now override or merge with those specified in a ruleset.xml file in all cases
- PHPCS now stops looking for a phpcs.xml file as soon as one is found, favoring the closest one to the current dir
- Added missing help text for the --stdin-path CLI option to --help
- Re-added missing help text for the --file-list and --bootstrap CLI options to --help
- Runner::runPHPCS() and Runner::runPHPCBF() now return an exit code instead of exiting directly (request [#1484][sq-1484])
- The Squiz standard now enforces short array syntax by default
- The autoloader is now working correctly with classes created with class_alias()
- The autoloader will now search for files inside all directories in the installed_paths config var
    - This allows autoloading of files inside included custom coding standards without manually requiring them
- You can now specify a namespace for a custom coding standard, used by the autoloader to load non-sniff helper files
    - Also used by the autoloader to help other standards directly include sniffs for your standard
    - Set the value to the namespace prefix you are using for sniff files (everything up to \Sniffs\)
    - e.g., if your namespace format is MyProject\CS\Standard\Sniffs\Category set the namespace to MyProject\CS\Standard
    - If omitted, the namespace is assumed to be the same as the directory name containing the ruleset.xml file
    - The namespace is set in the ruleset tag of the ruleset.xml file
    - e.g., ruleset name="My Coding Standard" namespace="MyProject\CS\Standard"
- Rulesets can now specify custom autoloaders using the new autoload tag
    - Autoloaders are included while the ruleset is being processed and before any custom sniffs are included
    - Allows for very custom autoloading of helper classes well before the boostrap files are included
- The PEAR standard now includes Squiz.Commenting.DocCommentAlignment
    - It previously broke comments onto multiple lines, but didn't align them

### Fixed
- Fixed a problem where excluding a message from a custom standard's own sniff would exclude the whole sniff
    - This caused some PSR2 errors to be under-reported
- Fixed bug [#1442][sq-1442] : T_NULLABLE detection not working for nullable parameters and return type hints in some cases
- Fixed bug [#1447][sq-1447] : Running the unit tests with a PHPUnit config file breaks the test suite
    - Unknown arguments were not being handled correctly, but are now stored in $config->unknown
- Fixed bug [#1449][sq-1449] : Generic.Classes.OpeningBraceSameLine doesn't detect comment before opening brace
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1450][sq-1450] : Coding standard located under an installed_path with the same directory name throws an error
    - Thanks to [Juliette Reinders Folmer][@jrfnl] for the patch
- Fixed bug [#1451][sq-1451] : Sniff exclusions/restrictions don't work with custom sniffs unless they use the PHP_CodeSniffer NS
- Fixed bug [#1454][sq-1454] : Squiz.WhiteSpace.OperatorSpacing is not checking spacing on either side of a short ternary operator
    - Thanks to [Mponos George][@gmponos] for the patch
- Fixed bug [#1495][sq-1495] : Setting an invalid installed path breaks all commands
- Fixed bug [#1496][sq-1496] : Squiz.Strings.DoubleQuoteUsage not unescaping dollar sign when fixing
    - Thanks to [Michał Bundyra][@michalbundyra] for the patch
- Fixed bug [#1501][sq-1501] : Interactive mode is broken
- Fixed bug [#1504][sq-1504] : PSR2.Namespaces.UseDeclaration hangs fixing use statement with no trailing code

[sq-1442]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1442
[sq-1447]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1447
[sq-1449]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1449
[sq-1450]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1450
[sq-1451]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1451
[sq-1454]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1454
[sq-1484]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1484
[sq-1495]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1495
[sq-1496]: https://github.com/squizlabs/PHP_CodeSniffer/pull/1496
[sq-1501]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1501
[sq-1504]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1504


## [3.0.0] - 2017-05-04

### Changed
- Added an --ignore-annotations command line argument to ignore all @codingStandards annotations in code comments (request [#811][sq-811])
- This allows you to force errors to be shown that would otherwise be ignored by code comments
    - Also stop files being able to change sniff properties midway through processing
- An error is now reported if no sniffs were registered to be run (request [#1129][sq-1129])
- The autoloader will now search for files inside the directory of any loaded coding standard
    - This allows autoloading of any file inside a custom coding standard without manually requiring them
    - Ensure your namespace begins with your coding standard's directory name and follows PSR-4
    - e.g., StandardName\Sniffs\CategoryName\AbstractHelper or StandardName\Helpers\StringSniffHelper
- Fixed an error where STDIN was sometimes not checked when using the --parallel CLI option
- The is_closure index has been removed from the return value of File::getMethodProperties()
    - This value was always false because T_FUNCTION tokens are never closures
    - Closures have a token type of T_CLOSURE
- The File::isAnonymousFunction() method has been removed
    - This function always returned false because it only accepted T_FUNCTION tokens, which are never closures
    - Closures have a token type of T_CLOSURE
- Includes all changes from the 2.9.0 release

### Fixed
- Fixed bug [#834][sq-834] : PSR2.ControlStructures.SwitchDeclaration does not handle if branches with returns
    - Thanks to [Fabian Wiget][@fabacino] for the patch

[sq-811]: https://github.com/squizlabs/PHP_CodeSniffer/issues/811
[sq-834]: https://github.com/squizlabs/PHP_CodeSniffer/issues/834
[sq-1129]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1129

## [3.0.0RC4] - 2017-03-02

### Security
- This release contains a fix for a security advisory related to the improper handling of shell commands
    - Uses of shell_exec() and exec() were not escaping filenames and configuration settings in most cases
    - A properly crafted filename or configuration option would allow for arbitrary code execution when using some features
    - All users are encouraged to upgrade to this version, especially if you are checking 3rd-party code
        - e.g., you run PHPCS over libraries that you did not write
        - e.g., you provide a web service that runs PHPCS over user-uploaded files or 3rd-party repositories
        - e.g., you allow external tool paths to be set by user-defined values
    - If you are unable to upgrade but you check 3rd-party code, ensure you are not using the following features:
        - The diff report
        - The notify-send report
        - The Generic.PHP.Syntax sniff
        - The Generic.Debug.CSSLint sniff
        - The Generic.Debug.ClosureLinter sniff
        - The Generic.Debug.JSHint sniff
        - The Squiz.Debug.JSLint sniff
        - The Squiz.Debug.JavaScriptLint sniff
        - The Zend.Debug.CodeAnalyzer sniff
    - Thanks to [Klaus Purer][@klausi] for the report

### Changed
- The indent property of PEAR.Classes.ClassDeclaration has been removed
    - Instead of calculating the indent of the brace, it just ensures the brace is aligned with the class keyword
    - Other sniffs can be used to ensure the class itself is indented correctly
- Invalid exclude rules inside a ruleset.xml file are now ignored instead of potentially causing out of memory errors
    - Using the -vv command line argument now also shows the invalid exclude rule as XML
- Includes all changes from the 2.8.1 release

### Fixed
- Fixed bug [#1333][sq-1333] : The new autoloader breaks some frameworks with custom autoloaders
- Fixed bug [#1334][sq-1334] : Undefined offset when explaining standard with custom sniffs

[sq-1333]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1333
[sq-1334]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1334

## [3.0.0RC3] - 2017-02-02

### Changed
- Added support for ES6 class declarations
    - Previously, these class were tokenized as JS objects but are now tokenized as normal T_CLASS structures
- Added support for ES6 method declarations, where the "function" keyword is not used
    - Previously, these methods were tokenized as JS objects (fixes bug [#1251][sq-1251])
    - The name of the ES6 method is now assigned the T_FUNCTION keyword and treated like a normal function
    - Custom sniffs that support JS and listen for T_FUNCTION tokens can't assume the token represents the word "function"
    - Check the contents of the token first, or use $phpcsFile->getDeclarationName($stackPtr) if you just want its name
    - There is no change for custom sniffs that only check PHP code
- PHPCBF exit codes have been changed so they are now more useful (request [#1270][sq-1270])
    - Exit code 0 is now used to indicate that no fixable errors were found, and so nothing was fixed
    - Exit code 1 is now used to indicate that all fixable errors were fixed correctly
    - Exit code 2 is now used to indicate that PHPCBF failed to fix some of the fixable errors it found
    - Exit code 3 is now used for general script execution errors
- Added PEAR.Commenting.FileComment.ParamCommentAlignment to check alignment of multi-line param comments
- Includes all changes from the 2.8.0 release

### Fixed
- Fixed an issue where excluding a file using a @codingStandardsIgnoreFile comment would produce errors
    - For PHPCS, it would show empty files being processed
    - For PHPCBF, it would produce a PHP error
- Fixed bug [#1233][sq-1233] : Can't set config data inside ruleset.xml file
- Fixed bug [#1241][sq-1241] : CodeSniffer.conf not working with 3.x PHAR file

[sq-1233]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1233
[sq-1241]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1241
[sq-1251]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1251
[sq-1270]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1270

## [3.0.0RC2] - 2016-11-30

### Changed
- Made the Runner class easier to use with wrapper scripts
- Full usage information is no longer printed when a usage error is encountered (request [#1186][sq-1186])
    - Makes it a lot easier to find and read the error message that was printed
- Includes all changes from the 2.7.1 release

### Fixed
- Fixed an undefined var name error that could be produced while running PHPCBF
- Fixed bug [#1167][sq-1167] : 3.0.0RC1 PHAR does not work with PEAR standard
- Fixed bug [#1208][sq-1208] : Excluding files doesn't work when using STDIN with a filename specified

[sq-1167]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1167
[sq-1186]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1186
[sq-1208]: https://github.com/squizlabs/PHP_CodeSniffer/issues/1208

## [3.0.0RC1] - 2016-09-02

### Changed
- Progress output now shows E and W in green when a file has fixable errors or warnings
    - Only supported if colors are enabled
- PHPCBF no longer produces verbose output by default (request [#699][sq-699])
    - Use the -v command line argument to show verbose fixing output
    - Use the -q command line argument to disable verbose information if enabled by default
- PHPBF now prints a summary report after fixing files
    - Report shows files that were fixed, how many errors were fixed, and how many remain
- PHPCBF now supports the -p command line argument to print progress information
    - Prints a green F for files where fixes occurred
    - Prints a red E for files that could not be fixed due to an error
    - Use the -q command line argument to disable progress information if enabled by default
- Running unit tests using --verbose no longer throws errors
- Includes all changes from the 2.7.0 release

### Fixed
- Fixed shell error appearing on some systems when trying to find executable paths

[sq-699]: https://github.com/squizlabs/PHP_CodeSniffer/issues/699

## [3.0.0a1] - 2016-07-20

### Changed
- Min PHP version increased from 5.1.2 to 5.4.0
- Added optional caching of results between runs (request [#530][sq-530])
    - Enable the cache by using the --cache command line argument
    - If you want the cache file written somewhere specific, use --cache=/path/to/cacheFile
    - Use the command "phpcs --config-set cache true" to turn caching on by default
    - Use the --no-cache command line argument to disable caching if it is being turned on automatically
- Add support for checking file in parallel (request [#421][sq-421])
    - Tell PHPCS how many files to check at once using the --parallel command line argument
    - To check 100 files at once, using --parallel=100
    - To disable parallel checking if it is being turned on automatically, use --parallel=1
    - Requires PHP to be compiled with the PCNTL package
- The default encoding has been changed from iso-8859-1 to utf-8 (request [#760][sq-760])
    - The --encoding command line argument still works, but you no longer have to set it to process files as utf-8
    - If encoding is being set to utf-8 in a ruleset or on the CLI, it can be safely removed
    - If the iconv PHP extension is not installed, standard non-multibyte aware functions will be used
- Added a new "code" report type to show a code snippet for each error (request [#419][sq-419])
    - The line containing the error is printed, along with 2 lines above and below it to show context
    - The location of the errors is underlined in the code snippet if you also use --colors
    - Use --report=code to generate this report
- Added support for custom filtering of the file list
    - Developers can write their own filter classes to perform custom filtering of the list before the run starts
    - Use the command line arg `--filter=/path/to/filter.php` to specify a filter to use
    - Extend \PHP_CodeSniffer\Filters\Filter to also support the core PHPCS extension and path filtering
    - Extend \PHP_CodeSniffer\Filters\ExactMatch to get the core filtering and the ability to use blacklists and whitelists
    - The included \PHP_CodeSniffer\Filters\GitModified filter is a good example of an ExactMatch filter
- Added support for only checking files that have been locally modified or added in a git repo
    - Use --filter=gitmodified to check these files
    - You still need to give PHPCS a list of files or directories in which to check
- Added automatic discovery of executable paths (request [#571][sq-571])
    - Thanks to [Sergei Morozov][@morozov] for the patch
- You must now pass "-" on the command line to have PHPCS wait for STDIN
    - E.g., phpcs --standard=PSR2 -
    - You can still pipe content via STDIN as normal as PHPCS will see this and process it
    - But without the "-", PHPCS will throw an error if no content or files are passed to it
- All PHP errors generated by sniffs are caught, re-thrown as exceptions, and reported in the standard error reports
    - This should stop bugs inside sniffs causing infinite loops
    - Also stops invalid reports being produced as errors don't print to the screen directly
- Sniff codes are no longer optional
    - If a sniff throws an error or a warning, it must specify an internal code for that message
- The installed_paths config setting can now point directly to a standard
    - Previously, it had to always point to the directory in which the standard lives
- Multiple reports can now be specified using the --report command line argument
    - Report types are separated by commas
    - E.g., --report=full,summary,info
    - Previously, you had to use one argument for each report such as --report=full --report=summary --report=info
- You can now set the severity, message type, and exclude patterns for an entire sniff, category, or standard
    - Previously, this was only available for a single message
- You can now include a single sniff code in a ruleset instead of having to include an entire sniff
    - Including a sniff code will automatically exclude all other messages from that sniff
    - If the sniff is already included by an imported standard, set the sniff severity to 0 and include the specific message you want
- PHPCBF no longer uses patch
    - Files are now always overwritten
    - The --no-patch option has been removed
- Added a --basepath option to strip a directory from the front of file paths in output (request [#470][sq-470])
    - The basepath is absolute or relative to the current directory
    - E.g., to output paths relative to current dir in reports, use --basepath=.
- Ignore rules are now checked when using STDIN (request [#733][sq-733])
- Added an include-pattern tag to rulesets to include a sniff for specific files and folders only (request [#656][sq-656])
    - This is the exact opposite of the exclude-pattern tag
    - This option is only usable within sniffs, not globally like exclude-patterns are
- Added a new -m option to stop error messages from being recorded, which saves a lot of memory
    - PHPCBF always uses this setting to reduce memory as it never outputs error messages
    - Setting the $recordErrors member var inside custom report classes is no longer supported (use -m instead)
- Exit code 2 is now used to indicate fixable errors were found (request [#930][sq-930])
    - Exit code 3 is now used for general script execution errors
    - Exit code 1 is used to indicate that coding standard errors were found, but none are fixable
    - Exit code 0 is unchanged and continues to mean no coding standard errors found

### Removed
- The included PHPCS standard has been removed
    - All rules are now found inside the phpcs.xml.dist file
    - Running "phpcs" without any arguments from a git clone will use this ruleset
- The included SVN pre-commit hook has been removed
    - Hooks for version control systems will no longer be maintained within the PHPCS project

[sq-419]: https://github.com/squizlabs/PHP_CodeSniffer/issues/419
[sq-421]: https://github.com/squizlabs/PHP_CodeSniffer/issues/421
[sq-470]: https://github.com/squizlabs/PHP_CodeSniffer/issues/470
[sq-530]: https://github.com/squizlabs/PHP_CodeSniffer/issues/530
[sq-571]: https://github.com/squizlabs/PHP_CodeSniffer/pull/571
[sq-656]: https://github.com/squizlabs/PHP_CodeSniffer/issues/656
[sq-733]: https://github.com/squizlabs/PHP_CodeSniffer/issues/733
[sq-760]: https://github.com/squizlabs/PHP_CodeSniffer/issues/760
[sq-930]: https://github.com/squizlabs/PHP_CodeSniffer/issues/930


<!--
=== Link list for release links ====
-->

[3.13.5]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.13.4...3.13.5
[3.13.4]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.13.3...3.13.4
[3.13.3]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.13.2...3.13.3
[3.13.2]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.13.1...3.13.2
[3.13.1]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.13.0...3.13.1
[3.13.0]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.12.2...3.13.0
[3.12.2]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.12.1...3.12.2
[3.12.1]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.12.0...3.12.1
[3.12.0]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.11.3...3.12.0
[3.11.3]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.11.2...3.11.3
[3.11.2]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.11.1...3.11.2
[3.11.1]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.11.0...3.11.1
[3.11.0]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.10.3...3.11.0
[3.10.3]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.10.2...3.10.3
[3.10.2]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.10.1...3.10.2
[3.10.1]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.10.0...3.10.1
[3.10.0]:     https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.9.2...3.10.0
[3.9.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.9.1...3.9.2
[3.9.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.9.0...3.9.1
[3.9.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.8.1...3.9.0
[3.8.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.8.0...3.8.1
[3.8.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.7.2...3.8.0
[3.7.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.7.1...3.7.2
[3.7.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.7.0...3.7.1
[3.7.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.6.2...3.7.0
[3.6.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.6.1...3.6.2
[3.6.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.6.0...3.6.1
[3.6.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.8...3.6.0
[3.5.8]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.7...3.5.8
[3.5.7]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.6...3.5.7
[3.5.6]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.5...3.5.6
[3.5.5]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.4...3.5.5
[3.5.4]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.3...3.5.4
[3.5.3]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.2...3.5.3
[3.5.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.1...3.5.2
[3.5.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.5.0...3.5.1
[3.5.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.4.2...3.5.0
[3.4.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.4.1...3.4.2
[3.4.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.4.0...3.4.1
[3.4.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.3.2...3.4.0
[3.3.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.3.1...3.3.2
[3.3.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.3.0...3.3.1
[3.3.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.2.3...3.3.0
[3.2.3]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.2.2...3.2.3
[3.2.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.2.1...3.2.2
[3.2.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.2.0...3.2.1
[3.2.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.1.1...3.2.0
[3.1.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.1.0...3.1.1
[3.1.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.2...3.1.0
[3.0.2]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.1...3.0.2
[3.0.1]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.0...3.0.1
[3.0.0]:      https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.0RC4...3.0.0
[3.0.0RC4]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.0RC3...3.0.0RC4
[3.0.0RC3]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.0RC2...3.0.0RC3
[3.0.0RC2]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.0RC1...3.0.0RC2
[3.0.0RC1]:   https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/3.0.0a1...3.0.0RC1
[3.0.0a1]:    https://github.com/PHPCSStandards/PHP_CodeSniffer/compare/2.9.2...3.0.0a1

<!--
=== Link list for contributor profiles ====
-->

[@aboks]:               https://github.com/aboks
[@afilina]:             https://github.com/afilina
[@alekitto]:            https://github.com/alekitto
[@anbuc]:               https://github.com/anbuc
[@AndrewDawes]:         https://github.com/AndrewDawes
[@andrewnicols]:        https://github.com/andrewnicols
[@andypost]:            https://github.com/andypost
[@annechko]:            https://github.com/annechko
[@anomiex]:             https://github.com/anomiex
[@arnested]:            https://github.com/arnested
[@asispts]:             https://github.com/asispts
[@axlon]:               https://github.com/axlon
[@Benjamin-Loison]:     https://github.com/Benjamin-Loison
[@benno5020]:           https://github.com/benno5020
[@biinari]:             https://github.com/biinari
[@Billz95]:             https://github.com/Billz95
[@blue32a]:             https://github.com/blue32a
[@bondas83]:            https://github.com/bondas83
[@braindawg]:           https://github.com/braindawg
[@cixtor]:              https://github.com/cixtor
[@codebymikey]:         https://github.com/codebymikey
[@costdev]:             https://github.com/costdev
[@Daimona]:             https://github.com/Daimona
[@DanielEScherzer]:     https://github.com/DanielEScherzer
[@DannyvdSluijs]:       https://github.com/DannyvdSluijs
[@datengraben]:         https://github.com/datengraben
[@derrabus]:            https://github.com/derrabus
[@devfrey]:             https://github.com/devfrey
[@dhensby]:             https://github.com/dhensby
[@dingo-d]:             https://github.com/dingo-d
[@duncan3dc]:           https://github.com/duncan3dc
[@edorian]:             https://github.com/edorian
[@emil-nasso]:          https://github.com/emil-nasso
[@enl]:                 https://github.com/enl
[@exussum12]:           https://github.com/exussum12
[@fabacino]:            https://github.com/fabacino
[@fcool]:               https://github.com/fcool
[@filips123]:           https://github.com/filips123
[@Fischer-Bjoern]:      https://github.com/Fischer-Bjoern
[@fredden]:             https://github.com/fredden
[@GaryJones]:           https://github.com/GaryJones
[@ghostal]:             https://github.com/ghostal
[@gmponos]:             https://github.com/gmponos
[@grongor]:             https://github.com/grongor
[@gwharton]:            https://github.com/gwharton
[@iammattcoleman]:      https://github.com/iammattcoleman
[@ivuorinen]:           https://github.com/ivuorinen
[@javer]:               https://github.com/javer
[@jaymcp]:              https://github.com/jaymcp
[@joachim-n]:           https://github.com/joachim-n
[@johnpbloch]:          https://github.com/johnpbloch
[@JorisDebonnet]:       https://github.com/JorisDebonnet
[@josephzidell]:        https://github.com/josephzidell
[@jpoliveira08]:        https://github.com/jpoliveira08
[@jpuck]:               https://github.com/jpuck
[@jrfnl]:               https://github.com/jrfnl
[@klausi]:              https://github.com/klausi
[@kukulich]:            https://github.com/kukulich
[@legoktm]:             https://github.com/legoktm
[@lmanzke]:             https://github.com/lmanzke
[@lucc]:                https://github.com/lucc
[@Majkl578]:            https://github.com/Majkl578
[@marcospassos]:        https://github.com/marcospassos
[@MarkBaker]:           https://github.com/MarkBaker
[@martinssipenko]:      https://github.com/martinssipenko
[@marvasDE]:            https://github.com/marvasDE
[@maryo]:               https://github.com/maryo
[@MasterOdin]:          https://github.com/MasterOdin
[@MatmaRex]:            https://github.com/MatmaRex
[@mhujer]:              https://github.com/mhujer
[@michalbundyra]:       https://github.com/michalbundyra
[@Morerice]:            https://github.com/Morerice
[@mbomb007]:            https://github.com/mbomb007
[@morozov]:             https://github.com/morozov
[@NanoSector]:          https://github.com/NanoSector
[@ndm2]:                https://github.com/ndm2
[@nicholascus]:         https://github.com/nicholascus
[@nkovacs]:             https://github.com/nkovacs
[@o5]:                  https://github.com/o5
[@ostrolucky]:          https://github.com/ostrolucky
[@peterwilsoncc]:       https://github.com/peterwilsoncc
[@pfrenssen]:           https://github.com/pfrenssen
[@phil-davis]:          https://github.com/phil-davis
[@photodude]:           https://github.com/photodude
[@przemekhernik]:       https://github.com/przemekhernik
[@raul338]:             https://github.com/raul338
[@remicollet]:          https://github.com/remicollet
[@renaatdemuynck]:      https://github.com/renaatdemuynck
[@rhorber]:             https://github.com/rhorber
[@rodrigoprimo]:        https://github.com/rodrigoprimo
[@schlessera]:          https://github.com/schlessera
[@shivammathur]:        https://github.com/shivammathur
[@simonsan]:            https://github.com/simonsan
[@SteveTalbot]:         https://github.com/SteveTalbot
[@stronk7]:             https://github.com/stronk7
[@svycka]:              https://github.com/svycka
[@TomHAnderson]:        https://github.com/TomHAnderson
[@thewilkybarkid]:      https://github.com/thewilkybarkid
[@thiemowmde]:          https://github.com/thiemowmde
[@timoschinkel]:        https://github.com/timoschinkel
[@TimWolla]:            https://github.com/TimWolla
[@VincentLanglet]:      https://github.com/VincentLanglet
[@willemstuursma]:      https://github.com/willemstuursma
[@wvega]:               https://github.com/wvega
[@xalopp]:              https://github.com/xalopp
[@xjm]:                 https://github.com/xjm
