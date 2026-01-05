<?php

/**
 * Stores the rules used to check and fix files.
 *
 * A ruleset object directly maps to a ruleset XML file.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer;

use InvalidArgumentException;
use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Sniffs\DeprecatedSniff;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\MessageCollector;
use PHP_CodeSniffer\Util\Standards;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SimpleXMLElement;
use stdClass;
class Ruleset
{
    /**
     * The name of the coding standard being used.
     *
     * If a top-level standard includes other standards, or sniffs
     * from other standards, only the name of the top-level standard
     * will be stored in here.
     *
     * If multiple top-level standards are being loaded into
     * a single ruleset object, this will store a comma separated list
     * of the top-level standard names.
     *
     * @var string
     */
    public $name = '';
    /**
     * A list of file paths for the ruleset files being used.
     *
     * @var string[]
     */
    public $paths = [];
    /**
     * A list of regular expressions used to ignore specific sniffs for files and folders.
     *
     * Is also used to set global exclude patterns.
     * The key is the regular expression and the value is the type
     * of ignore pattern (absolute or relative).
     *
     * @var array<string, array>
     */
    public $ignorePatterns = [];
    /**
     * A list of regular expressions used to include specific sniffs for files and folders.
     *
     * The key is the sniff code and the value is an array with
     * the key being a regular expression and the value is the type
     * of ignore pattern (absolute or relative).
     *
     * @var array<string, array<string, string>>
     */
    public $includePatterns = [];
    /**
     * An array of sniff objects that are being used to check files.
     *
     * The key is the fully qualified name of the sniff class
     * and the value is the sniff object.
     *
     * @var array<string, \PHP_CodeSniffer\Sniffs\Sniff>
     */
    public $sniffs = [];
    /**
     * A mapping of sniff codes to fully qualified class names.
     *
     * The key is the sniff code and the value
     * is the fully qualified name of the sniff class.
     *
     * @var array<string, string>
     */
    public $sniffCodes = [];
    /**
     * An array of token types and the sniffs that are listening for them.
     *
     * The key is the token name being listened for and the value
     * is the sniff object.
     *
     * @var array<int, array<string, array<string, mixed>>>
     */
    public $tokenListeners = [];
    /**
     * An array of rules from the ruleset.xml file.
     *
     * It may be empty, indicating that the ruleset does not override
     * any of the default sniff settings.
     *
     * @var array<string, mixed>
     */
    public $ruleset = [];
    /**
     * The directories that the processed rulesets are in.
     *
     * @var string[]
     */
    protected $rulesetDirs = [];
    /**
     * The config data for the run.
     *
     * @var \PHP_CodeSniffer\Config
     */
    private $config = null;
    /**
     * The `<config>` directives which have been applied.
     *
     * @var array<string, int> Key is the name of the config. Value is the ruleset depth
     *                         at which this config was applied (if not overruled from the CLI).
     */
    private $configDirectivesApplied = [];
    /**
     * The `<arg>` directives which have been applied.
     *
     * @var array<string, int> Key is the name of the setting. Value is the ruleset depth
     *                         at which this setting was applied (if not overruled from the CLI).
     */
    private $cliSettingsApplied = [];
    /**
     * An array of the names of sniffs which have been marked as deprecated.
     *
     * The key is the sniff code and the value
     * is the fully qualified name of the sniff class.
     *
     * @var array<string, string>
     */
    private $deprecatedSniffs = [];
    /**
     * Message collector object.
     *
     * User-facing messages should be collected via this object for display once the ruleset processing has finished.
     *
     * The following type of errors should *NOT* be collected, but should still throw their own `RuntimeException`:
     * - Errors which could cause other (uncollectable) errors further into the ruleset processing, like a missing autoload file.
     * - Errors which are directly aimed at and only intended for sniff developers or integrators
     *   (in contrast to ruleset maintainers or end-users).
     *
     * @var \PHP_CodeSniffer\Util\MessageCollector
     */
    private $msgCache;
    /**
     * Initialise the ruleset that the run will use.
     *
     * @param \PHP_CodeSniffer\Config $config The config data for the run.
     *
     * @return void
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException If blocking errors were encountered when processing the ruleset.
     */
    public function __construct(\PHP_CodeSniffer\Config $config)
    {
        $this->config = $config;
        $restrictions = $config->sniffs;
        $exclusions = $config->exclude;
        $sniffs = [];
        $this->msgCache = new MessageCollector();
        $standardPaths = [];
        foreach ($config->standards as $standard) {
            $installed = Standards::getInstalledStandardPath($standard);
            if ($installed === null) {
                $standard = Common::realpath($standard);
                if (\is_dir($standard) === \true && \is_file(Common::realpath($standard . \DIRECTORY_SEPARATOR . 'ruleset.xml')) === \true) {
                    $standard = Common::realpath($standard . \DIRECTORY_SEPARATOR . 'ruleset.xml');
                }
            } else {
                $standard = $installed;
            }
            $standardPaths[] = $standard;
        }
        foreach ($standardPaths as $standard) {
            $ruleset = @\simplexml_load_string(\file_get_contents($standard));
            if ($ruleset !== \false) {
                $standardName = (string) $ruleset['name'];
                if ($this->name !== '') {
                    $this->name .= ', ';
                }
                $this->name .= $standardName;
                // Allow autoloading of custom files inside this standard.
                if (isset($ruleset['namespace']) === \true) {
                    $namespace = (string) $ruleset['namespace'];
                } else {
                    $namespace = \basename(\dirname($standard));
                }
                \PHP_CodeSniffer\Autoload::addSearchPath(\dirname($standard), $namespace);
            }
            if (\PHP_CODESNIFFER_VERBOSITY === 1) {
                $newlines = 0;
                if (\count($config->standards) > 1 || \PHP_CODESNIFFER_VERBOSITY > 2) {
                    $newlines = 1;
                }
                StatusWriter::write("Registering sniffs in the {$standardName} standard... ", 0, $newlines);
            }
            $sniffs = \array_merge($sniffs, $this->processRuleset($standard));
        }
        // Ignore sniff restrictions if caching is on.
        if ($config->cache === \true) {
            $restrictions = [];
            $exclusions = [];
        }
        $sniffRestrictions = [];
        foreach ($restrictions as $sniffCode) {
            $parts = \explode('.', \strtolower($sniffCode));
            $sniffName = $parts[0] . '\\sniffs\\' . $parts[1] . '\\' . $parts[2] . 'sniff';
            $sniffRestrictions[$sniffName] = \true;
        }
        $sniffExclusions = [];
        foreach ($exclusions as $sniffCode) {
            $parts = \explode('.', \strtolower($sniffCode));
            $sniffName = $parts[0] . '\\sniffs\\' . $parts[1] . '\\' . $parts[2] . 'sniff';
            $sniffExclusions[$sniffName] = \true;
        }
        $this->registerSniffs($sniffs, $sniffRestrictions, $sniffExclusions);
        $this->populateTokenListeners();
        $numSniffs = \count($this->sniffs);
        if (\PHP_CODESNIFFER_VERBOSITY === 1) {
            StatusWriter::write("DONE ({$numSniffs} sniffs registered)");
        }
        if ($numSniffs === 0) {
            $this->msgCache->add('No sniffs were registered.', MessageCollector::ERROR);
        }
        $this->displayCachedMessages();
    }
    /**
     * Prints a report showing the sniffs contained in a standard.
     *
     * @return void
     */
    public function explain()
    {
        $sniffs = \array_keys($this->sniffCodes);
        \sort($sniffs, \SORT_NATURAL | \SORT_FLAG_CASE);
        $sniffCount = \count($sniffs);
        // Add a dummy entry to the end so we loop one last time
        // and echo out the collected info about the last standard.
        $sniffs[] = '';
        $summaryLine = \PHP_EOL . "The {$this->name} standard contains 1 sniff" . \PHP_EOL;
        if ($sniffCount !== 1) {
            $summaryLine = \str_replace('1 sniff', "{$sniffCount} sniffs", $summaryLine);
        }
        echo $summaryLine;
        $lastStandard = null;
        $lastCount = 0;
        $sniffsInStandard = [];
        foreach ($sniffs as $i => $sniff) {
            if ($i === $sniffCount) {
                $currentStandard = null;
            } else {
                $currentStandard = (string) \substr($sniff, 0, \strpos($sniff, '.'));
                if ($lastStandard === null) {
                    $lastStandard = $currentStandard;
                }
            }
            // Reached the first item in the next standard.
            // Echo out the info collected from the previous standard.
            if ($currentStandard !== $lastStandard) {
                $subTitle = $lastStandard . ' (' . $lastCount . ' sniff';
                if ($lastCount > 1) {
                    $subTitle .= 's';
                }
                $subTitle .= ')';
                echo \PHP_EOL . $subTitle . \PHP_EOL;
                echo \str_repeat('-', \strlen($subTitle)) . \PHP_EOL;
                echo '  ' . \implode(\PHP_EOL . '  ', $sniffsInStandard) . \PHP_EOL;
                $lastStandard = $currentStandard;
                $lastCount = 0;
                $sniffsInStandard = [];
                if ($currentStandard === null) {
                    break;
                }
            }
            if (isset($this->deprecatedSniffs[$sniff]) === \true) {
                $sniff .= ' *';
            }
            $sniffsInStandard[] = $sniff;
            ++$lastCount;
        }
        if (\count($this->deprecatedSniffs) > 0) {
            echo \PHP_EOL . '* Sniffs marked with an asterisk are deprecated.' . \PHP_EOL;
        }
    }
    /**
     * Checks whether any deprecated sniffs were registered via the ruleset.
     *
     * @return bool
     */
    public function hasSniffDeprecations()
    {
        return \count($this->deprecatedSniffs) > 0;
    }
    /**
     * Prints an information block about deprecated sniffs being used.
     *
     * @return void
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException When the interface implementation is faulty.
     */
    public function showSniffDeprecations()
    {
        if ($this->hasSniffDeprecations() === \false) {
            return;
        }
        // Don't show deprecation notices in quiet mode, in explain mode
        // or when the documentation is being shown.
        // Documentation and explain will mark a sniff as deprecated natively
        // and also call the Ruleset multiple times which would lead to duplicate
        // display of the deprecation messages.
        if ($this->config->quiet === \true || $this->config->explain === \true || $this->config->generator !== null) {
            return;
        }
        $reportWidth = $this->config->reportWidth;
        // Message takes report width minus the leading dash + two spaces, minus a one space gutter at the end.
        $maxMessageWidth = $reportWidth - 4;
        $maxActualWidth = 0;
        \ksort($this->deprecatedSniffs, \SORT_NATURAL | \SORT_FLAG_CASE);
        $messages = [];
        $messageTemplate = 'This sniff has been deprecated since %s and will be removed in %s. %s';
        $errorTemplate = 'ERROR: The %s::%s() method must return a %sstring, received %s';
        foreach ($this->deprecatedSniffs as $sniffCode => $className) {
            if (isset($this->sniffs[$className]) === \false) {
                // Should only be possible in test situations, but some extra defensive coding is never a bad thing.
                continue;
            }
            // Verify the interface was implemented correctly.
            // Unfortunately can't be safeguarded via type declarations yet.
            $deprecatedSince = $this->sniffs[$className]->getDeprecationVersion();
            if (\is_string($deprecatedSince) === \false) {
                throw new RuntimeException(\sprintf($errorTemplate, $className, 'getDeprecationVersion', 'non-empty ', \gettype($deprecatedSince)));
            }
            if ($deprecatedSince === '') {
                throw new RuntimeException(\sprintf($errorTemplate, $className, 'getDeprecationVersion', 'non-empty ', '""'));
            }
            $removedIn = $this->sniffs[$className]->getRemovalVersion();
            if (\is_string($removedIn) === \false) {
                throw new RuntimeException(\sprintf($errorTemplate, $className, 'getRemovalVersion', 'non-empty ', \gettype($removedIn)));
            }
            if ($removedIn === '') {
                throw new RuntimeException(\sprintf($errorTemplate, $className, 'getRemovalVersion', 'non-empty ', '""'));
            }
            $customMessage = $this->sniffs[$className]->getDeprecationMessage();
            if (\is_string($customMessage) === \false) {
                throw new RuntimeException(\sprintf($errorTemplate, $className, 'getDeprecationMessage', '', \gettype($customMessage)));
            }
            // Truncate the error code if there is not enough report width.
            if (\strlen($sniffCode) > $maxMessageWidth) {
                $sniffCode = \substr($sniffCode, 0, $maxMessageWidth - 3) . '...';
            }
            $message = '-  ' . "\x1b[36m" . $sniffCode . "\x1b[0m" . \PHP_EOL;
            $maxActualWidth = \max($maxActualWidth, \strlen($sniffCode));
            // Normalize new line characters in custom message.
            $customMessage = \preg_replace('`\\R`', \PHP_EOL, $customMessage);
            $notice = \trim(\sprintf($messageTemplate, $deprecatedSince, $removedIn, $customMessage));
            $maxActualWidth = \max($maxActualWidth, \min(\strlen($notice), $maxMessageWidth));
            $wrapped = \wordwrap($notice, $maxMessageWidth, \PHP_EOL);
            $message .= '   ' . \implode(\PHP_EOL . '   ', \explode(\PHP_EOL, $wrapped));
            $messages[] = $message;
        }
        if (\count($messages) === 0) {
            return;
        }
        $summaryLine = "WARNING: The {$this->name} standard uses 1 deprecated sniff";
        $sniffCount = \count($messages);
        if ($sniffCount !== 1) {
            $summaryLine = \str_replace('1 deprecated sniff', "{$sniffCount} deprecated sniffs", $summaryLine);
        }
        $maxActualWidth = \max($maxActualWidth, \min(\strlen($summaryLine), $maxMessageWidth));
        $summaryLine = \wordwrap($summaryLine, $reportWidth, \PHP_EOL);
        if ($this->config->colors === \true) {
            StatusWriter::write("\x1b[33m" . $summaryLine . "\x1b[0m");
        } else {
            StatusWriter::write($summaryLine);
        }
        $messages = \implode(\PHP_EOL, $messages);
        if ($this->config->colors === \false) {
            $messages = Common::stripColors($messages);
        }
        StatusWriter::write(\str_repeat('-', \min($maxActualWidth + 4, $reportWidth)));
        StatusWriter::write($messages, 0, 0);
        $closer = \wordwrap('Deprecated sniffs are still run, but will stop working at some point in the future.', $reportWidth, \PHP_EOL);
        StatusWriter::writeNewline(2);
        StatusWriter::write($closer, 0, 2);
    }
    /**
     * Print any notices encountered while processing the ruleset(s).
     *
     * Note: these messages aren't shown at the time they are encountered to avoid "one error hiding behind another".
     * This way the (end-)user gets to see all of them in one go.
     *
     * @return void
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException If blocking errors were encountered.
     */
    private function displayCachedMessages()
    {
        // Don't show deprecations/notices/warnings in quiet mode, in explain mode
        // or when the documentation is being shown.
        // Documentation and explain will call the Ruleset multiple times which
        // would lead to duplicate display of the messages.
        if ($this->msgCache->containsBlockingErrors() === \false && ($this->config->quiet === \true || $this->config->explain === \true || $this->config->generator !== null)) {
            return;
        }
        $this->msgCache->display();
    }
    /**
     * Processes a single ruleset and returns a list of the sniffs it represents.
     *
     * Rules founds within the ruleset are processed immediately, but sniff classes
     * are not registered by this method.
     *
     * @param string $rulesetPath The path to a ruleset XML file.
     * @param int    $depth       How many nested processing steps we are in. This
     *                            is only used for debug output.
     *
     * @return string[]
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException - If the ruleset path is invalid.
     *                                                      - If a specified autoload file could not be found.
     */
    public function processRuleset(string $rulesetPath, int $depth = 0)
    {
        $rulesetPath = Common::realpath($rulesetPath);
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('Processing ruleset ' . Common::stripBasepath($rulesetPath, $this->config->basepath), $depth);
        }
        \libxml_use_internal_errors(\true);
        $ruleset = \simplexml_load_string(\file_get_contents($rulesetPath));
        if ($ruleset === \false) {
            $errorMsg = "ERROR: Ruleset {$rulesetPath} is not valid" . \PHP_EOL;
            $errors = \libxml_get_errors();
            foreach ($errors as $error) {
                $errorMsg .= '- On line ' . $error->line . ', column ' . $error->column . ': ' . $error->message;
            }
            \libxml_clear_errors();
            throw new RuntimeException($errorMsg);
        }
        \libxml_use_internal_errors(\false);
        $ownSniffs = [];
        $includedSniffs = [];
        $excludedSniffs = [];
        $this->paths[] = $rulesetPath;
        $rulesetDir = \dirname($rulesetPath);
        $this->rulesetDirs[] = $rulesetDir;
        $sniffDir = $rulesetDir . \DIRECTORY_SEPARATOR . 'Sniffs';
        if (\is_dir($sniffDir) === \true) {
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('Adding sniff files from ' . Common::stripBasepath($sniffDir, $this->config->basepath) . ' directory', $depth + 1);
            }
            $ownSniffs = $this->expandSniffDirectory($sniffDir, $depth);
        }
        // Include custom autoloaders.
        foreach ($ruleset->{'autoload'} as $autoload) {
            if ($this->shouldProcessElement($autoload) === \false) {
                continue;
            }
            $autoloadPath = (string) $autoload;
            // Try relative autoload paths first.
            $relativePath = Common::realpath(\dirname($rulesetPath) . \DIRECTORY_SEPARATOR . $autoloadPath);
            if ($relativePath !== \false && \is_file($relativePath) === \true) {
                $autoloadPath = $relativePath;
            } elseif (\is_file($autoloadPath) === \false) {
                throw new RuntimeException('ERROR: The specified autoload file "' . $autoload . '" does not exist');
            }
            include_once $autoloadPath;
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("=> included autoloader {$autoloadPath}", $depth + 1);
            }
        }
        // Process custom sniff config settings.
        // Processing rules:
        // - Highest level ruleset take precedence.
        // - If the same config is being set from two rulesets at the same level, *last* one "wins".
        foreach ($ruleset->{'config'} as $config) {
            if ($this->shouldProcessElement($config) === \false) {
                continue;
            }
            $name = (string) $config['name'];
            if (isset($this->configDirectivesApplied[$name]) === \true && $this->configDirectivesApplied[$name] < $depth) {
                // Ignore this config. A higher level ruleset has already set a value for this directive.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('=> ignoring config value ' . $name . ': ' . (string) $config['value'] . ' => already changed by a higher level ruleset ', $depth + 1);
                }
                continue;
            }
            $this->configDirectivesApplied[$name] = $depth;
            $applied = $this->config->setConfigData($name, (string) $config['value'], \true);
            if ($applied === \true && \PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('=> set config value ' . $name . ': ' . (string) $config['value'], $depth + 1);
            }
        }
        // Process custom command line arguments.
        // Processing rules:
        // - Highest level ruleset take precedence.
        // - If the same CLI flag is being set from two rulesets at the same level, *first* one "wins".
        $cliArgs = [];
        foreach ($ruleset->{'arg'} as $arg) {
            if ($this->shouldProcessElement($arg) === \false) {
                continue;
            }
            // "Long" CLI argument. Arg is in the format `<arg name="name" [value="value"]/>`.
            if (isset($arg['name']) === \true) {
                $name = (string) $arg['name'];
                $cliSettingName = $name;
                if (isset($this->config::CLI_FLAGS_TO_SETTING_NAME[$name]) === \true) {
                    $cliSettingName = $this->config::CLI_FLAGS_TO_SETTING_NAME[$name];
                }
                if (isset($this->cliSettingsApplied[$cliSettingName]) === \true && $this->cliSettingsApplied[$cliSettingName] < $depth) {
                    // Ignore this CLI flag. A higher level ruleset has already set a value for this setting.
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $statusMessage = '=> ignoring command line arg --' . $name;
                        if (isset($arg['value']) === \true) {
                            $statusMessage .= '=' . (string) $arg['value'];
                        }
                        StatusWriter::write($statusMessage . ' => already changed by a higher level ruleset ', $depth + 1);
                    }
                    continue;
                }
                // Remember which settings we've seen.
                $this->cliSettingsApplied[$cliSettingName] = $depth;
                $argString = '--' . $name;
                if (isset($arg['value']) === \true) {
                    $argString .= '=' . (string) $arg['value'];
                }
            } else {
                // "Short" CLI flag. Arg is in the format `<arg value="value"/>` and
                // value can contain multiple flags, like `<arg value="ps"/>`.
                $cleanedValue = '';
                $cliFlagsAsArray = \str_split((string) $arg['value']);
                foreach ($cliFlagsAsArray as $flag) {
                    $cliSettingName = $flag;
                    if (isset($this->config::CLI_FLAGS_TO_SETTING_NAME[$flag]) === \true) {
                        $cliSettingName = $this->config::CLI_FLAGS_TO_SETTING_NAME[$flag];
                    }
                    if (isset($this->cliSettingsApplied[$cliSettingName]) === \true && $this->cliSettingsApplied[$cliSettingName] < $depth) {
                        // Ignore this CLI flag. A higher level ruleset has already set a value for this setting.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('=> ignoring command line flag -' . $flag . ' => already changed by a higher level ruleset ', $depth + 1);
                        }
                        continue;
                    }
                    // Remember which settings we've seen.
                    $cleanedValue .= $flag;
                    $this->cliSettingsApplied[$cliSettingName] = $depth;
                }
                if ($cleanedValue === '') {
                    // No flags found which should be applied.
                    continue;
                }
                $argString = '-' . $cleanedValue;
            }
            $cliArgs[] = $argString;
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("=> set command line value {$argString}", $depth + 1);
            }
        }
        foreach ($ruleset->rule as $rule) {
            if (isset($rule['ref']) === \false || $this->shouldProcessElement($rule) === \false) {
                continue;
            }
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('Processing rule "' . $rule['ref'] . '"', $depth + 1);
            }
            $expandedSniffs = $this->expandRulesetReference((string) $rule['ref'], $rulesetDir, $depth);
            $newSniffs = \array_diff($expandedSniffs, $includedSniffs);
            $includedSniffs = \array_merge($includedSniffs, $expandedSniffs);
            $parts = \explode('.', $rule['ref']);
            if (\count($parts) === 4 && $parts[0] !== '' && $parts[1] !== '' && $parts[2] !== '') {
                $sniffCode = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
                if (isset($this->ruleset[$sniffCode]['severity']) === \true && $this->ruleset[$sniffCode]['severity'] === 0) {
                    // This sniff code has already been turned off, but now
                    // it is being explicitly included again, so turn it back on.
                    $this->ruleset[(string) $rule['ref']]['severity'] = 5;
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* disabling sniff exclusion for specific message code *', $depth + 2);
                        StatusWriter::write('=> severity set to 5', $depth + 2);
                    }
                } elseif (empty($newSniffs) === \false) {
                    $newSniff = $newSniffs[0];
                    if (\in_array($newSniff, $ownSniffs, \true) === \false) {
                        // Including a sniff that hasn't been included higher up, but
                        // only including a single message from it. So turn off all messages in
                        // the sniff, except this one.
                        $this->ruleset[$sniffCode]['severity'] = 0;
                        $this->ruleset[(string) $rule['ref']]['severity'] = 5;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('Excluding sniff "' . $sniffCode . '" except for "' . $parts[3] . '"', $depth + 2);
                        }
                    }
                }
            }
            if (isset($rule->exclude) === \true) {
                foreach ($rule->exclude as $exclude) {
                    if (isset($exclude['name']) === \false) {
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('* ignoring empty exclude rule *', $depth + 2);
                            StatusWriter::write('=> ' . $exclude->asXML(), $depth + 3);
                        }
                        continue;
                    }
                    if ($this->shouldProcessElement($exclude) === \false) {
                        continue;
                    }
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('Excluding rule "' . $exclude['name'] . '"', $depth + 2);
                    }
                    // Check if a single code is being excluded, which is a shortcut
                    // for setting the severity of the message to 0.
                    $parts = \explode('.', $exclude['name']);
                    if (\count($parts) === 4) {
                        $this->ruleset[(string) $exclude['name']]['severity'] = 0;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('=> severity set to 0', $depth + 2);
                        }
                    } else {
                        $excludedSniffs = \array_merge($excludedSniffs, $this->expandRulesetReference((string) $exclude['name'], $rulesetDir, $depth + 1));
                    }
                }
            }
            $this->processRule($rule, $newSniffs, $depth);
        }
        // Set custom php ini values as CLI args.
        foreach ($ruleset->{'ini'} as $arg) {
            if ($this->shouldProcessElement($arg) === \false) {
                continue;
            }
            if (isset($arg['name']) === \false) {
                continue;
            }
            $name = (string) $arg['name'];
            $argString = $name;
            if (isset($arg['value']) === \true) {
                $value = (string) $arg['value'];
                $argString .= "={$value}";
            } else {
                $value = 'true';
            }
            $cliArgs[] = '-d';
            $cliArgs[] = $argString;
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("=> set PHP ini value {$name} to {$value}", $depth + 1);
            }
        }
        if (empty($this->config->files) === \true) {
            // Process hard-coded file paths.
            foreach ($ruleset->{'file'} as $file) {
                $file = (string) $file;
                $cliArgs[] = $file;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write("=> added \"{$file}\" to the file list", $depth + 1);
                }
            }
        }
        if (empty($cliArgs) === \false) {
            // Change the directory so all relative paths are worked
            // out based on the location of the ruleset instead of
            // the location of the user.
            $inPhar = Common::isPharFile($rulesetDir);
            if ($inPhar === \false) {
                $currentDir = \getcwd();
                \chdir($rulesetDir);
            }
            $this->config->setCommandLineValues($cliArgs);
            if ($inPhar === \false) {
                \chdir($currentDir);
            }
        }
        // Process custom ignore pattern rules.
        foreach ($ruleset->{'exclude-pattern'} as $pattern) {
            if ($this->shouldProcessElement($pattern) === \false) {
                continue;
            }
            if (isset($pattern['type']) === \false) {
                $pattern['type'] = 'absolute';
            }
            $this->ignorePatterns[(string) $pattern] = (string) $pattern['type'];
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('=> added global ' . (string) $pattern['type'] . ' ignore pattern: ' . (string) $pattern, $depth + 1);
            }
        }
        $includedSniffs = \array_unique(\array_merge($ownSniffs, $includedSniffs));
        $excludedSniffs = \array_unique($excludedSniffs);
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $included = \count($includedSniffs);
            $excluded = \count($excludedSniffs);
            StatusWriter::write("=> Ruleset processing complete; included {$included} sniffs and excluded {$excluded}", $depth);
        }
        // Merge our own sniff list with our externally included
        // sniff list, but filter out any excluded sniffs.
        $files = [];
        foreach ($includedSniffs as $sniff) {
            if (\in_array($sniff, $excludedSniffs, \true) === \true) {
                continue;
            } else {
                $files[] = Common::realpath($sniff);
            }
        }
        return $files;
    }
    /**
     * Expands a directory into a list of sniff files within.
     *
     * @param string $directory The path to a directory.
     * @param int    $depth     How many nested processing steps we are in. This
     *                          is only used for debug output.
     *
     * @return array
     */
    private function expandSniffDirectory(string $directory, int $depth = 0)
    {
        $sniffs = [];
        $rdi = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $di = new RecursiveIteratorIterator($rdi, 0, RecursiveIteratorIterator::CATCH_GET_CHILD);
        $dirLen = \strlen($directory);
        foreach ($di as $file) {
            $filename = $file->getFilename();
            // Skip hidden files.
            if (\substr($filename, 0, 1) === '.') {
                continue;
            }
            // We are only interested in PHP and sniff files.
            $fileParts = \explode('.', $filename);
            if (\array_pop($fileParts) !== 'php') {
                continue;
            }
            $basename = \basename($filename, '.php');
            if (\substr($basename, -5) !== 'Sniff') {
                continue;
            }
            $path = $file->getPathname();
            // Skip files in hidden directories within the Sniffs directory of this
            // standard. We use the offset with strpos() to allow hidden directories
            // before, valid example:
            // /home/foo/.composer/vendor/squiz/custom_tool/MyStandard/Sniffs/...
            if (\strpos($path, \DIRECTORY_SEPARATOR . '.', $dirLen) !== \false) {
                continue;
            }
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('=> ' . Common::stripBasepath($path, $this->config->basepath), $depth + 2);
            }
            $sniffs[] = $path;
        }
        return $sniffs;
    }
    /**
     * Expands a ruleset reference into a list of sniff files.
     *
     * @param string $ref        The reference from the ruleset XML file.
     * @param string $rulesetDir The directory of the ruleset XML file, used to
     *                           evaluate relative paths.
     * @param int    $depth      How many nested processing steps we are in. This
     *                           is only used for debug output.
     *
     * @return array
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException If the reference is invalid.
     */
    private function expandRulesetReference(string $ref, string $rulesetDir, int $depth = 0)
    {
        // Naming an (external) standard "Internal" is not supported.
        if (\strtolower($ref) === 'internal') {
            $message = 'The name "Internal" is reserved for internal use. A PHP_CodeSniffer standard should not be called "Internal".' . \PHP_EOL;
            $message .= 'Contact the maintainer of the standard to fix this.';
            $this->msgCache->add($message, MessageCollector::ERROR);
            return [];
        }
        // Ignore internal sniffs codes as they are used to only
        // hide and change internal messages.
        if (\substr($ref, 0, 9) === 'Internal.') {
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('* ignoring internal sniff code *', $depth + 2);
            }
            return [];
        }
        // As sniffs can't begin with a full stop, assume references in
        // this format are relative paths and attempt to convert them
        // to absolute paths. If this fails, let the reference run through
        // the normal checks and have it fail as normal.
        if (\substr($ref, 0, 1) === '.') {
            $realpath = Common::realpath($rulesetDir . '/' . $ref);
            if ($realpath !== \false) {
                $ref = $realpath;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('=> ' . Common::stripBasepath($ref, $this->config->basepath), $depth + 2);
                }
            }
        }
        // As sniffs can't begin with a tilde, assume references in
        // this format are relative to the user's home directory.
        if (\substr($ref, 0, 2) === '~/') {
            $realpath = Common::realpath($ref);
            if ($realpath !== \false) {
                $ref = $realpath;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('=> ' . Common::stripBasepath($ref, $this->config->basepath), $depth + 2);
                }
            }
        }
        if (\is_file($ref) === \true) {
            if (\substr($ref, -9) === 'Sniff.php') {
                // A single external sniff.
                $this->rulesetDirs[] = \dirname($ref, 3);
                return [$ref];
            }
        } else {
            // See if this is a whole standard being referenced.
            $path = Standards::getInstalledStandardPath($ref);
            if ($path !== null && Common::isPharFile($path) === \true && \strpos($path, 'ruleset.xml') === \false) {
                // If the ruleset exists inside the phar file, use it.
                if (\file_exists($path . \DIRECTORY_SEPARATOR . 'ruleset.xml') === \true) {
                    $path .= \DIRECTORY_SEPARATOR . 'ruleset.xml';
                } else {
                    $path = null;
                }
            }
            if ($path !== null) {
                $ref = $path;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('=> ' . Common::stripBasepath($ref, $this->config->basepath), $depth + 2);
                }
            } elseif (\is_dir($ref) === \false) {
                // Work out the sniff path.
                $sepPos = \strpos($ref, \DIRECTORY_SEPARATOR);
                if ($sepPos !== \false) {
                    $stdName = (string) \substr($ref, 0, $sepPos);
                    $path = (string) \substr($ref, $sepPos);
                } else {
                    $parts = \explode('.', $ref);
                    $stdName = $parts[0];
                    if (\count($parts) === 1) {
                        // A whole standard?
                        $path = '';
                    } elseif (\count($parts) === 2) {
                        // A directory of sniffs?
                        $path = \DIRECTORY_SEPARATOR . 'Sniffs' . \DIRECTORY_SEPARATOR . $parts[1];
                    } else {
                        // A single sniff?
                        $path = \DIRECTORY_SEPARATOR . 'Sniffs' . \DIRECTORY_SEPARATOR . $parts[1] . \DIRECTORY_SEPARATOR . $parts[2] . 'Sniff.php';
                    }
                }
                $newRef = \false;
                $stdPath = Standards::getInstalledStandardPath($stdName);
                if ($stdPath !== null && $path !== '') {
                    if (Common::isPharFile($stdPath) === \true && \strpos($stdPath, 'ruleset.xml') === \false) {
                        // Phar files can only return the directory,
                        // since ruleset can be omitted if building one standard.
                        $newRef = Common::realpath($stdPath . $path);
                    } else {
                        $newRef = Common::realpath(\dirname($stdPath) . $path);
                    }
                }
                if ($newRef === \false) {
                    // The sniff is not locally installed, so check if it is being
                    // referenced as a remote sniff outside the install. We do this
                    // by looking through all directories where we have found ruleset
                    // files before, looking for ones for this particular standard,
                    // and seeing if it is in there.
                    foreach ($this->rulesetDirs as $dir) {
                        if (\strtolower(\basename($dir)) !== \strtolower($stdName)) {
                            continue;
                        }
                        $newRef = Common::realpath($dir . $path);
                        if ($newRef !== \false) {
                            $ref = $newRef;
                        }
                    }
                } else {
                    $ref = $newRef;
                }
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('=> ' . Common::stripBasepath($ref, $this->config->basepath), $depth + 2);
                }
            }
        }
        if (\is_dir($ref) === \true) {
            if (\is_file($ref . \DIRECTORY_SEPARATOR . 'ruleset.xml') === \true) {
                // We are referencing an external coding standard.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* rule is referencing a standard using directory name; processing *', $depth + 2);
                }
                return $this->processRuleset($ref . \DIRECTORY_SEPARATOR . 'ruleset.xml', $depth + 2);
            } else {
                // We are referencing a whole directory of sniffs.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* rule is referencing a directory of sniffs *', $depth + 2);
                    StatusWriter::write('Adding sniff files from directory', $depth + 2);
                }
                return $this->expandSniffDirectory($ref, $depth + 1);
            }
        } else {
            if (\is_file($ref) === \false) {
                $this->msgCache->add("Referenced sniff \"{$ref}\" does not exist.", MessageCollector::ERROR);
                return [];
            }
            if (\substr($ref, -9) === 'Sniff.php') {
                // A single sniff.
                return [$ref];
            } else {
                // Assume an external ruleset.xml file.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* rule is referencing a standard using ruleset path; processing *', $depth + 2);
                }
                return $this->processRuleset($ref, $depth + 2);
            }
        }
    }
    /**
     * Processes a rule from a ruleset XML file, overriding built-in defaults.
     *
     * @param \SimpleXMLElement $rule      The rule object from a ruleset XML file.
     * @param string[]          $newSniffs An array of sniffs that got included by this rule.
     * @param int               $depth     How many nested processing steps we are in.
     *                                     This is only used for debug output.
     *
     * @return void
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException If rule settings are invalid.
     */
    private function processRule(SimpleXMLElement $rule, array $newSniffs, int $depth = 0)
    {
        $ref = (string) $rule['ref'];
        $todo = [$ref];
        $parts = \explode('.', $ref);
        $partsCount = \count($parts);
        if ($partsCount <= 2 || $partsCount > \count(\array_filter($parts)) || \in_array($ref, $newSniffs) === \true) {
            // We are processing a standard, a category of sniffs or a relative path inclusion.
            foreach ($newSniffs as $sniffFile) {
                $parts = \explode(\DIRECTORY_SEPARATOR, $sniffFile);
                if (\count($parts) === 1 && \DIRECTORY_SEPARATOR === '\\') {
                    // Path using forward slashes while running on Windows.
                    $parts = \explode('/', $sniffFile);
                }
                $sniffName = \array_pop($parts);
                $sniffCategory = \array_pop($parts);
                \array_pop($parts);
                $sniffStandard = \array_pop($parts);
                $todo[] = $sniffStandard . '.' . $sniffCategory . '.' . \substr($sniffName, 0, -9);
            }
        }
        foreach ($todo as $code) {
            // Custom severity.
            if (isset($rule->severity) === \true && $this->shouldProcessElement($rule->severity) === \true) {
                if (isset($this->ruleset[$code]) === \false) {
                    $this->ruleset[$code] = [];
                }
                $this->ruleset[$code]['severity'] = (int) $rule->severity;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $statusMessage = '=> severity set to ' . (int) $rule->severity;
                    if ($code !== $ref) {
                        $statusMessage .= " for {$code}";
                    }
                    StatusWriter::write($statusMessage, $depth + 2);
                }
            }
            // Custom message type.
            if (isset($rule->type) === \true && $this->shouldProcessElement($rule->type) === \true) {
                if (isset($this->ruleset[$code]) === \false) {
                    $this->ruleset[$code] = [];
                }
                $type = \strtolower((string) $rule->type);
                if ($type !== 'error' && $type !== 'warning') {
                    $message = "Message type \"{$type}\" for \"{$code}\" is invalid; must be \"error\" or \"warning\".";
                    $this->msgCache->add($message, MessageCollector::ERROR);
                } else {
                    $this->ruleset[$code]['type'] = $type;
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $statusMessage = '=> message type set to ' . (string) $rule->type;
                        if ($code !== $ref) {
                            $statusMessage .= " for {$code}";
                        }
                        StatusWriter::write($statusMessage, $depth + 2);
                    }
                }
            }
            // Custom message.
            if (isset($rule->message) === \true && $this->shouldProcessElement($rule->message) === \true) {
                if (isset($this->ruleset[$code]) === \false) {
                    $this->ruleset[$code] = [];
                }
                $this->ruleset[$code]['message'] = (string) $rule->message;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $statusMessage = '=> message set to ' . (string) $rule->message;
                    if ($code !== $ref) {
                        $statusMessage .= " for {$code}";
                    }
                    StatusWriter::write($statusMessage, $depth + 2);
                }
            }
            // Custom properties.
            if (isset($rule->properties) === \true && $this->shouldProcessElement($rule->properties) === \true) {
                $propertyScope = 'standard';
                if ($code === $ref || \substr($ref, -9) === 'Sniff.php') {
                    $propertyScope = 'sniff';
                }
                foreach ($rule->properties->property as $prop) {
                    if ($this->shouldProcessElement($prop) === \false) {
                        continue;
                    }
                    if (isset($this->ruleset[$code]) === \false) {
                        $this->ruleset[$code] = ['properties' => []];
                    } elseif (isset($this->ruleset[$code]['properties']) === \false) {
                        $this->ruleset[$code]['properties'] = [];
                    }
                    $name = (string) $prop['name'];
                    if (isset($prop['type']) === \true && (string) $prop['type'] === 'array') {
                        if (isset($prop['value']) === \true) {
                            $message = 'Passing an array of values to a property using a comma-separated string' . \PHP_EOL;
                            $message .= 'is no longer supported since PHP_CodeSniffer 4.0.0.' . \PHP_EOL;
                            $message .= "The unsupported syntax was used for property \"{$name}\"" . \PHP_EOL;
                            $message .= "for sniff \"{$code}\"." . \PHP_EOL;
                            $message .= 'Pass array values via <element [key="..." ]value="..."> nodes instead.';
                            $this->msgCache->add($message, MessageCollector::ERROR);
                            continue;
                        }
                        $values = [];
                        $extend = \false;
                        if (isset($prop['extend']) === \true && (string) $prop['extend'] === 'true') {
                            if (isset($this->ruleset[$code]['properties'][$name]['value']) === \true) {
                                $values = $this->ruleset[$code]['properties'][$name]['value'];
                                $extend = $this->ruleset[$code]['properties'][$name]['extend'];
                            } else {
                                $extend = \true;
                            }
                        }
                        if (isset($prop->element) === \true) {
                            $printValue = '';
                            foreach ($prop->element as $element) {
                                if ($this->shouldProcessElement($element) === \false) {
                                    continue;
                                }
                                $value = (string) $element['value'];
                                if (isset($element['key']) === \true) {
                                    $key = (string) $element['key'];
                                    $values[$key] = $value;
                                    $printValue .= $key . '=>' . $value . ',';
                                } else {
                                    $values[] = $value;
                                    $printValue .= $value . ',';
                                }
                            }
                            $printValue = \rtrim($printValue, ',');
                        }
                        $this->ruleset[$code]['properties'][$name] = ['value' => $values, 'scope' => $propertyScope, 'extend' => $extend];
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $statusMessage = "=> array property \"{$name}\" set to \"{$printValue}\"";
                            if ($code !== $ref) {
                                $statusMessage .= " for {$code}";
                            }
                            StatusWriter::write($statusMessage, $depth + 2);
                        }
                    } else {
                        $this->ruleset[$code]['properties'][$name] = ['value' => (string) $prop['value'], 'scope' => $propertyScope];
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $statusMessage = "=> property \"{$name}\" set to \"" . (string) $prop['value'] . '"';
                            if ($code !== $ref) {
                                $statusMessage .= " for {$code}";
                            }
                            StatusWriter::write($statusMessage, $depth + 2);
                        }
                    }
                }
            }
            // Ignore patterns.
            foreach ($rule->{'exclude-pattern'} as $pattern) {
                if ($this->shouldProcessElement($pattern) === \false) {
                    continue;
                }
                if (isset($this->ignorePatterns[$code]) === \false) {
                    $this->ignorePatterns[$code] = [];
                }
                if (isset($pattern['type']) === \false) {
                    $pattern['type'] = 'absolute';
                }
                $this->ignorePatterns[$code][(string) $pattern] = (string) $pattern['type'];
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $statusMessage = '=> added rule-specific ' . (string) $pattern['type'] . ' ignore pattern';
                    if ($code !== $ref) {
                        $statusMessage .= " for {$code}";
                    }
                    StatusWriter::write($statusMessage . ': ' . (string) $pattern, $depth + 2);
                }
            }
            // Include patterns.
            foreach ($rule->{'include-pattern'} as $pattern) {
                if ($this->shouldProcessElement($pattern) === \false) {
                    continue;
                }
                if (isset($this->includePatterns[$code]) === \false) {
                    $this->includePatterns[$code] = [];
                }
                if (isset($pattern['type']) === \false) {
                    $pattern['type'] = 'absolute';
                }
                $this->includePatterns[$code][(string) $pattern] = (string) $pattern['type'];
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $statusMessage = '=> added rule-specific ' . (string) $pattern['type'] . ' include pattern';
                    if ($code !== $ref) {
                        $statusMessage .= " for {$code}";
                    }
                    StatusWriter::write($statusMessage . ': ' . (string) $pattern, $depth + 2);
                }
            }
        }
    }
    /**
     * Determine if an element should be processed or ignored.
     *
     * @param \SimpleXMLElement $element An object from a ruleset XML file.
     *
     * @return bool
     */
    private function shouldProcessElement(SimpleXMLElement $element)
    {
        if (isset($element['phpcbf-only']) === \false && isset($element['phpcs-only']) === \false) {
            // No exceptions are being made.
            return \true;
        }
        if (\PHP_CODESNIFFER_CBF === \true && isset($element['phpcbf-only']) === \true && (string) $element['phpcbf-only'] === 'true') {
            return \true;
        }
        if (\PHP_CODESNIFFER_CBF === \false && isset($element['phpcs-only']) === \true && (string) $element['phpcs-only'] === 'true') {
            return \true;
        }
        return \false;
    }
    /**
     * Loads and stores sniffs objects used for sniffing files.
     *
     * @param array $files        Paths to the sniff files to register.
     * @param array $restrictions The sniff class names to restrict the allowed
     *                            listeners to.
     * @param array $exclusions   The sniff class names to exclude from the
     *                            listeners list.
     *
     * @return void
     */
    public function registerSniffs(array $files, array $restrictions, array $exclusions)
    {
        $listeners = [];
        foreach ($files as $file) {
            // Work out where the position of /StandardName/Sniffs/... is
            // so we can determine what the class will be called.
            $sniffPos = \strrpos($file, \DIRECTORY_SEPARATOR . 'Sniffs' . \DIRECTORY_SEPARATOR);
            if ($sniffPos === \false) {
                continue;
            }
            $slashPos = \strrpos((string) \substr($file, 0, $sniffPos), \DIRECTORY_SEPARATOR);
            if ($slashPos === \false) {
                continue;
            }
            $className = \PHP_CodeSniffer\Autoload::loadFile($file);
            $compareName = Common::cleanSniffClass($className);
            // If they have specified a list of sniffs to restrict to, check
            // to see if this sniff is allowed.
            if (empty($restrictions) === \false && isset($restrictions[$compareName]) === \false) {
                continue;
            }
            // If they have specified a list of sniffs to exclude, check
            // to see if this sniff is allowed.
            if (empty($exclusions) === \false && isset($exclusions[$compareName]) === \true) {
                continue;
            }
            // Skip abstract classes.
            $reflection = new ReflectionClass($className);
            if ($reflection->isAbstract() === \true) {
                continue;
            }
            if ($reflection->implementsInterface(Sniff::class) === \false) {
                $message = 'All sniffs must implement the PHP_CodeSniffer\\Sniffs\\Sniff interface.' . \PHP_EOL;
                $message .= "Interface not implemented for sniff {$className}." . \PHP_EOL;
                $message .= 'Contact the sniff author to fix the sniff.';
                $this->msgCache->add($message, MessageCollector::ERROR);
                continue;
            }
            if ($reflection->hasProperty('supportedTokenizers') === \true) {
                // Using the default value as the class is not yet instantiated and this is not a property which should get changed anyway.
                $value = $reflection->getDefaultProperties()['supportedTokenizers'];
                if (\is_array($value) === \true && empty($value) === \false && \in_array('PHP', $value, \true) === \false) {
                    if ($reflection->implementsInterface(DeprecatedSniff::class) === \true) {
                        // Silently ignore the sniff if the sniff is marked as deprecated.
                        continue;
                    }
                    $message = 'Support for scanning files other than PHP, like CSS/JS files, has been removed in PHP_CodeSniffer 4.0.' . \PHP_EOL;
                    $message .= 'The %s sniff is listening for %s.';
                    $message = \sprintf($message, Common::getSniffCode($className), \implode(', ', $value));
                    $this->msgCache->add($message, MessageCollector::ERROR);
                    continue;
                }
            }
            $listeners[$className] = $className;
            if (\PHP_CODESNIFFER_VERBOSITY > 2) {
                StatusWriter::write("Registered {$className}");
            }
        }
        $this->sniffs = $listeners;
    }
    /**
     * Populates the array of PHP_CodeSniffer_Sniff objects for this file.
     *
     * @return void
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException If sniff registration fails.
     */
    public function populateTokenListeners()
    {
        // Construct a list of listeners indexed by token being listened for.
        $this->tokenListeners = [];
        foreach ($this->sniffs as $sniffClass => $sniffObject) {
            try {
                $sniffCode = Common::getSniffCode($sniffClass);
            } catch (InvalidArgumentException $e) {
                $message = "The sniff {$sniffClass} does not comply with the PHP_CodeSniffer naming conventions." . \PHP_EOL;
                $message .= 'Contact the sniff author to fix the sniff.';
                $this->msgCache->add($message, MessageCollector::ERROR);
                // Unregister the sniff.
                unset($this->sniffs[$sniffClass]);
                continue;
            }
            $this->sniffs[$sniffClass] = new $sniffClass();
            $this->sniffCodes[$sniffCode] = $sniffClass;
            if ($this->sniffs[$sniffClass] instanceof DeprecatedSniff) {
                $this->deprecatedSniffs[$sniffCode] = $sniffClass;
            }
            // Set custom properties.
            if (isset($this->ruleset[$sniffCode]['properties']) === \true) {
                foreach ($this->ruleset[$sniffCode]['properties'] as $name => $settings) {
                    $this->setSniffProperty($sniffClass, $name, $settings);
                }
            }
            $tokens = $this->sniffs[$sniffClass]->register();
            if (\is_array($tokens) === \false) {
                $msg = "The sniff {$sniffClass}::register() method must return an array.";
                $this->msgCache->add($msg, MessageCollector::ERROR);
                // Unregister the sniff.
                unset($this->sniffs[$sniffClass], $this->sniffCodes[$sniffCode], $this->deprecatedSniffs[$sniffCode]);
                continue;
            }
            $ignorePatterns = [];
            $patterns = $this->getIgnorePatterns($sniffCode);
            foreach ($patterns as $pattern => $type) {
                $replacements = ['\\,' => ',', '*' => '.*'];
                $ignorePatterns[] = \strtr($pattern, $replacements);
            }
            $includePatterns = [];
            $patterns = $this->getIncludePatterns($sniffCode);
            foreach ($patterns as $pattern => $type) {
                $replacements = ['\\,' => ',', '*' => '.*'];
                $includePatterns[] = \strtr($pattern, $replacements);
            }
            foreach ($tokens as $token) {
                if (isset($this->tokenListeners[$token]) === \false) {
                    $this->tokenListeners[$token] = [];
                }
                if (isset($this->tokenListeners[$token][$sniffClass]) === \false) {
                    $this->tokenListeners[$token][$sniffClass] = ['class' => $sniffClass, 'source' => $sniffCode, 'ignore' => $ignorePatterns, 'include' => $includePatterns];
                }
            }
        }
    }
    /**
     * Set a single property for a sniff.
     *
     * @param string $sniffClass The class name of the sniff.
     * @param string $name       The name of the property to change.
     * @param array  $settings   Array with the new value of the property and the scope of the property being set.
     *                           May optionally include an `extend` key to indicate a pre-existing array value should be extended.
     *
     * @return void
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException When attempting to set a non-existent property on a sniff
     *                                                      which doesn't declare the property or explicitly supports
     *                                                      dynamic properties.
     */
    public function setSniffProperty(string $sniffClass, string $name, array $settings)
    {
        // Setting a property for a sniff we are not using.
        if (isset($this->sniffs[$sniffClass]) === \false) {
            return;
        }
        $name = \trim($name);
        $propertyName = $name;
        if (\substr($propertyName, -2) === '[]') {
            $propertyName = (string) \substr($propertyName, 0, -2);
        }
        $isSettable = \false;
        $sniffObject = $this->sniffs[$sniffClass];
        if (\property_exists($sniffObject, $propertyName) === \true || $sniffObject instanceof stdClass === \true || \method_exists($sniffObject, '__set') === \true) {
            $isSettable = \true;
        }
        if ($isSettable === \false) {
            if ($settings['scope'] === 'sniff') {
                $notice = "Property \"{$propertyName}\" does not exist on sniff ";
                $notice .= \array_search($sniffClass, $this->sniffCodes, \true) . '.';
                $this->msgCache->add($notice, MessageCollector::ERROR);
            }
            return;
        }
        $value = $settings['value'];
        // Handle properties set inline via phpcs:set.
        if (\substr($name, -2) === '[]') {
            $values = [];
            if (\is_string($value) === \true && \trim($value) !== '') {
                foreach (\explode(',', $value) as $val) {
                    if (\strpos($val, '=>') === \false) {
                        $values[] = $val;
                    } else {
                        list($k, $v) = \explode('=>', $val);
                        $values[\trim($k)] = $v;
                    }
                }
            }
            $value = $this->getRealPropertyValue($values);
        } else {
            $value = $this->getRealPropertyValue($value);
        }
        if (isset($settings['extend']) === \true && $settings['extend'] === \true && isset($sniffObject->{$propertyName}) === \true && \is_array($sniffObject->{$propertyName}) === \true && \is_array($value) === \true) {
            $sniffObject->{$propertyName} = \array_merge($sniffObject->{$propertyName}, $value);
        } else {
            $sniffObject->{$propertyName} = $value;
        }
    }
    /**
     * Transform a property value received via a ruleset or inline annotation to a typed value.
     *
     * @param string|array<int|string, string> $value The current property value.
     *
     * @return mixed
     */
    private function getRealPropertyValue($value)
    {
        if (\is_array($value) === \true) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->getRealPropertyValue($v);
            }
            return $value;
        }
        if (\is_string($value) === \true) {
            $value = \trim($value);
            if ($value === '') {
                return null;
            }
            $valueLc = \strtolower($value);
            if ($valueLc === 'true') {
                return \true;
            }
            if ($valueLc === 'false') {
                return \false;
            }
            if ($valueLc === 'null') {
                return null;
            }
        }
        return $value;
    }
    /**
     * Gets the array of ignore patterns.
     *
     * Optionally takes a listener to get ignore patterns specified
     * for that sniff only.
     *
     * @param string|null $listener The listener to get patterns for. If NULL, all
     *                              patterns are returned.
     *
     * @return array
     */
    public function getIgnorePatterns(?string $listener = null)
    {
        if ($listener === null) {
            return $this->ignorePatterns;
        }
        if (isset($this->ignorePatterns[$listener]) === \true) {
            return $this->ignorePatterns[$listener];
        }
        return [];
    }
    /**
     * Gets the array of include patterns.
     *
     * Optionally takes a listener to get include patterns specified
     * for that sniff only.
     *
     * @param string|null $listener The listener to get patterns for. If NULL, all
     *                              patterns are returned.
     *
     * @return array
     */
    public function getIncludePatterns(?string $listener = null)
    {
        if ($listener === null) {
            return $this->includePatterns;
        }
        if (isset($this->includePatterns[$listener]) === \true) {
            return $this->includePatterns[$listener];
        }
        return [];
    }
}
