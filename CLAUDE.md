# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Project

**Symplify EasyCodingStandard (ECS)** — a unified runner for PHP-CS-Fixer and PHP_CodeSniffer. Users configure rules through a fluent PHP API (`ECSConfig::configure()->with...()`) instead of dealing with each tool's native config format.

- PHP `>=8.3`
- Entry binary: `bin/ecs`
- PSR-4: `Symplify\EasyCodingStandard\` → `src/`
- Tests PSR-4: `Symplify\EasyCodingStandard\Tests\` → `tests/`

## Required workflow after every change

Run all three, in this order. Fix anything that fails before reporting work as done.

```bash
composer phpstan       # vendor/bin/phpstan analyse (level 8, src + tests + ecs.php + rector.php)
composer rector        # vendor/bin/rector process --dry-run
composer check-cs      # bin/ecs check
```

Shortcut for all three: `composer lint`.

To auto-apply Rector and ECS fixes: `composer lint.fix` (runs `fix-rector` then `fix-cs`).

Tests: `composer test` (PHPUnit). Run them when behavior may have changed.

Memory limit for PHPStan and Rector is `1G` (already set in composer scripts).

## Architecture

- `src/Config/ECSConfig.php` — Illuminate container subclass, low-level rule registration (`rule()`, `ruleWithConfiguration()`, `sets()`, `import()`). Auto-tags Sniff/FixerInterface/OutputFormatterInterface bindings.
- `src/Configuration/ECSConfigBuilder.php` — fluent user-facing API (`withRules`, `withSets`, `withPreparedSets`, `withPhpCsFixerSets`, `withSpacesLevel`, …). Returned by `ECSConfig::configure()`. `__invoke(ECSConfig)` flushes the builder state into the container.
- `config/set/common/*.php` — prepared rule sets (spaces, arrays, namespaces, docblock, etc.); each returns a closure consumed by `ECSConfig::import()`.
- `src/Config/Level/` — gradual-adoption levels (e.g. `SpacesLevel`). Each level class exposes `RULES` (ordered safest → most invasive) and optionally `RULE_CONFIGURATIONS`.
- `src/Configuration/Levels/LevelRulesResolver.php` — resolves `int $level` to the first N+1 rules from a level's `RULES` array. Clamps to max, throws on negative input or empty rule list.

### Adding a level (mirrors Rector's `withTypeCoverageLevel` pattern)

1. Create `src/Config/Level/<Name>Level.php` with `RULES` (ordered) and `RULE_CONFIGURATIONS` (only for configurable rules).
2. Add `with<Name>Level(int $level): self` to `ECSConfigBuilder`. Use `LevelRulesResolver::resolve()`, then route configurable rules to `$this->rulesWithConfiguration` and the rest to `$this->rules`.

## Conventions

- `declare(strict_types=1);` on every PHP file.
- `final` classes by default.
- PHPStan level 8, `type_coverage.return: 99`. Keep new code fully typed.
- Don't introduce new comments unless they explain a non-obvious why; well-named identifiers should carry meaning.
- Don't add backwards-compat shims, dead re-exports, or features that aren't required by the task.

## Patched dependency

`illuminate/container` is patched via `patches/illuminate-container-container-php.patch` (cweagans/composer-patches). Don't update the package without re-checking the patch.

## Vendor prefixing / release build

`scoper.php` and `prefix-code.sh` / `full_ecs_build.sh` produce the prefixed distribution. Don't edit `scoper.php` casually; it's excluded from PHPStan.

## Don't

- Don't bypass `phpstan`, `rector`, or `check-cs` with skip comments unless the user asks for it.
- Don't run `lint.fix` or `fix-cs` automatically when the user only asked for analysis — the autofix changes files.
- Don't push, force-push, or open PRs unless explicitly asked.
