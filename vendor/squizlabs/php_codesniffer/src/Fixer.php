<?php

/**
 * A helper class for fixing errors.
 *
 * Provides helper functions that act upon a token array and modify the file
 * content.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer;

use InvalidArgumentException;
use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
class Fixer
{
    /**
     * Is the fixer enabled and fixing a file?
     *
     * Sniffs should check this value to ensure they are not
     * doing extra processing to prepare for a fix when fixing is
     * not required.
     *
     * @var boolean
     */
    public $enabled = \false;
    /**
     * The number of times we have looped over a file.
     *
     * @var integer
     */
    public $loops = 0;
    /**
     * The file being fixed.
     *
     * @var \PHP_CodeSniffer\Files\File
     */
    private $currentFile = null;
    /**
     * The list of tokens that make up the file contents.
     *
     * This is a simplified list which just contains the token content and nothing
     * else. This is the array that is updated as fixes are made, not the file's
     * token array. Imploding this array will give you the file content back.
     *
     * @var array<int, string>
     */
    private $tokens = [];
    /**
     * A list of tokens that have already been fixed.
     *
     * We don't allow the same token to be fixed more than once each time
     * through a file as this can easily cause conflicts between sniffs.
     *
     * @var int[]
     */
    private $fixedTokens = [];
    /**
     * The last value of each fixed token.
     *
     * If a token is being "fixed" back to its last value, the fix is
     * probably conflicting with another.
     *
     * @var array<int, array<string, mixed>>
     */
    private $oldTokenValues = [];
    /**
     * A list of tokens that have been fixed during a changeset.
     *
     * All changes in changeset must be able to be applied, or else
     * the entire changeset is rejected.
     *
     * @var array<int, string>
     */
    private $changeset = [];
    /**
     * Is there an open changeset.
     *
     * @var boolean
     */
    private $inChangeset = \false;
    /**
     * Is the current fixing loop in conflict?
     *
     * @var boolean
     */
    private $inConflict = \false;
    /**
     * The actual number of fixes that have been performed.
     *
     * I.e. how many fixes were applied. This may be higher than the originally found
     * issues if the fixer from one sniff causes other sniffs to come into play in follow-up loops.
     * Example: if a brace is moved to a new line, the `ScopeIndent` sniff may need to ensure
     * the brace is indented correctly in the next loop.
     *
     * @var integer
     */
    private $numFixes = 0;
    /**
     * Starts fixing a new file.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being fixed.
     *
     * @return void
     */
    public function startFile(File $phpcsFile)
    {
        $this->currentFile = $phpcsFile;
        $this->numFixes = 0;
        $this->fixedTokens = [];
        $tokens = $phpcsFile->getTokens();
        $this->tokens = [];
        foreach ($tokens as $index => $token) {
            if (isset($token['orig_content']) === \true) {
                $this->tokens[$index] = $token['orig_content'];
            } else {
                $this->tokens[$index] = $token['content'];
            }
        }
    }
    /**
     * Attempt to fix the file by processing it until no fixes are made.
     *
     * @return boolean
     */
    public function fixFile()
    {
        $fixable = $this->currentFile->getFixableCount();
        if ($fixable === 0) {
            // Nothing to fix.
            return \false;
        }
        $this->enabled = \true;
        // Pause the StatusWriter to silence Tokenizer debug info about the file being retokenized for each loop.
        StatusWriter::pause();
        $this->loops = 0;
        while ($this->loops < 50) {
            // Only needed once file content has changed.
            $contents = $this->getContents();
            if (\PHP_CODESNIFFER_VERBOSITY > 2) {
                StatusWriter::forceWrite('---START FILE CONTENT---');
                $lines = \explode($this->currentFile->eolChar, $contents);
                $max = \strlen(\count($lines));
                foreach ($lines as $lineNum => $line) {
                    $lineNum++;
                    StatusWriter::forceWrite(\str_pad($lineNum, $max, ' ', \STR_PAD_LEFT) . '|' . $line);
                }
                StatusWriter::forceWrite('--- END FILE CONTENT ---');
            }
            $this->inConflict = \false;
            $this->currentFile->ruleset->populateTokenListeners();
            $this->currentFile->setContent($contents);
            $this->currentFile->process();
            $this->loops++;
            if (\PHP_CODESNIFFER_CBF === \true && \PHP_CODESNIFFER_VERBOSITY > 0) {
                StatusWriter::forceWrite("\r" . \str_repeat(' ', 80) . "\r", 0, 0);
                $statusMessage = "=> Fixing file: {$this->numFixes}/{$fixable} violations remaining [made {$this->loops} pass";
                if ($this->loops > 1) {
                    $statusMessage .= 'es';
                }
                $statusMessage .= ']... ';
                $newlines = 0;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $newlines = 1;
                }
                StatusWriter::forceWrite($statusMessage, 1, $newlines);
            }
            if ($this->numFixes === 0 && $this->inConflict === \false) {
                // Nothing left to do.
                break;
            } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::forceWrite("* fixed {$this->numFixes} violations, starting loop " . ($this->loops + 1) . ' *', 1);
            }
        }
        $this->enabled = \false;
        StatusWriter::resume();
        if ($this->numFixes > 0 || $this->inConflict === \true) {
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("*** Reached maximum number of loops with {$this->numFixes} violations left unfixed ***", 1);
            }
            return \false;
        }
        return \true;
    }
    /**
     * Generates a text diff of the original file and the new content.
     *
     * @param string|null $filePath Optional file path to diff the file against.
     *                              If not specified, the original version of the
     *                              file will be used.
     * @param boolean     $colors   Print coloured output or not.
     *
     * @return string
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException When the diff command fails.
     */
    public function generateDiff(?string $filePath = null, bool $colors = \true)
    {
        if ($filePath === null) {
            $filePath = $this->currentFile->getFilename();
        }
        $cwd = \getcwd() . \DIRECTORY_SEPARATOR;
        if (\strpos($filePath, $cwd) === 0) {
            $filename = (string) \substr($filePath, \strlen($cwd));
        } else {
            $filename = $filePath;
        }
        $contents = $this->getContents();
        $tempName = \tempnam(\sys_get_temp_dir(), 'phpcs-fixer');
        $fixedFile = \fopen($tempName, 'w');
        \fwrite($fixedFile, $contents);
        // We must use something like shell_exec() or proc_open() because whitespace at the end
        // of lines is critical to diff files.
        // Using proc_open() instead of shell_exec improves performance on Windows significantly,
        // while the results are the same (though more code is needed to get the results).
        // This is specifically due to proc_open allowing to set the "bypass_shell" option.
        $filename = \escapeshellarg($filename);
        $cmd = "diff -u -L{$filename} -LPHP_CodeSniffer {$filename} \"{$tempName}\"";
        // Stream 0 = STDIN, 1 = STDOUT, 2 = STDERR.
        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $options = null;
        if (\PHP_OS_FAMILY === 'Windows') {
            $options = ['bypass_shell' => \true];
        }
        $process = \proc_open($cmd, $descriptorspec, $pipes, $cwd, null, $options);
        if (\is_resource($process) === \false) {
            throw new RuntimeException('Could not obtain a resource to execute the diff command.');
        }
        // We don't need these.
        \fclose($pipes[0]);
        \fclose($pipes[2]);
        // Stdout will contain the actual diff.
        $diff = \stream_get_contents($pipes[1]);
        \fclose($pipes[1]);
        \proc_close($process);
        \fclose($fixedFile);
        if (\is_file($tempName) === \true) {
            \unlink($tempName);
        }
        if ($diff === \false || $diff === '') {
            return '';
        }
        if ($colors === \false) {
            return $diff;
        }
        $diffLines = \explode(\PHP_EOL, $diff);
        if (\count($diffLines) === 1) {
            // Seems to be required for cygwin.
            $diffLines = \explode("\n", $diff);
        }
        $diff = [];
        foreach ($diffLines as $line) {
            if (isset($line[0]) === \true) {
                switch ($line[0]) {
                    case '-':
                        $diff[] = "\x1b[31m{$line}\x1b[0m";
                        break;
                    case '+':
                        $diff[] = "\x1b[32m{$line}\x1b[0m";
                        break;
                    default:
                        $diff[] = $line;
                }
            }
        }
        $diff = \implode(\PHP_EOL, $diff);
        return $diff;
    }
    /**
     * Get a count of the actual number of fixes that have been performed on the file.
     *
     * This value is reset every time a new file is started, or an existing
     * file is restarted.
     *
     * @return int
     */
    public function getFixCount()
    {
        return $this->numFixes;
    }
    /**
     * Get the current content of the file, as a string.
     *
     * @return string
     */
    public function getContents()
    {
        $contents = \implode($this->tokens);
        return $contents;
    }
    /**
     * Get the current fixed content of a token.
     *
     * This function takes changesets into account so should be used
     * instead of directly accessing the token array.
     *
     * @param int $stackPtr The position of the token in the token stack.
     *
     * @return string
     */
    public function getTokenContent(int $stackPtr)
    {
        if ($this->inChangeset === \true && isset($this->changeset[$stackPtr]) === \true) {
            return $this->changeset[$stackPtr];
        } else {
            return $this->tokens[$stackPtr];
        }
    }
    /**
     * Start recording actions for a changeset.
     *
     * @return void|false
     */
    public function beginChangeset()
    {
        if ($this->inConflict === \true) {
            return \false;
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $bt = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
            if ($bt[1]['class'] === self::class) {
                $sniff = 'Fixer';
            } else {
                $sniff = $this->getSniffCodeForDebug($bt[1]['class']);
            }
            $line = $bt[0]['line'];
            StatusWriter::forceWrite("=> Changeset started by {$sniff}:{$line}", 1);
        }
        $this->changeset = [];
        $this->inChangeset = \true;
    }
    /**
     * Stop recording actions for a changeset, and apply logged changes.
     *
     * @return boolean
     */
    public function endChangeset()
    {
        if ($this->inConflict === \true) {
            return \false;
        }
        $this->inChangeset = \false;
        $success = \true;
        $applied = [];
        foreach ($this->changeset as $stackPtr => $content) {
            $success = $this->replaceToken($stackPtr, $content);
            if ($success === \false) {
                break;
            } else {
                $applied[] = $stackPtr;
            }
        }
        if ($success === \false) {
            // Rolling back all changes.
            foreach ($applied as $stackPtr) {
                $this->revertToken($stackPtr);
            }
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::forceWrite('=> Changeset failed to apply', 1);
            }
        } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $fixes = \count($this->changeset);
            StatusWriter::forceWrite("=> Changeset ended: {$fixes} changes applied", 1);
        }
        $this->changeset = [];
        return \true;
    }
    /**
     * Stop recording actions for a changeset, and discard logged changes.
     *
     * @return void
     */
    public function rollbackChangeset()
    {
        $this->inChangeset = \false;
        $this->inConflict = \false;
        if (empty($this->changeset) === \false) {
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                $bt = \debug_backtrace();
                if ($bt[1]['class'] === self::class) {
                    $sniff = $bt[2]['class'];
                    $line = $bt[1]['line'];
                } else {
                    $sniff = $bt[1]['class'];
                    $line = $bt[0]['line'];
                }
                $sniff = $this->getSniffCodeForDebug($sniff);
                $numChanges = \count($this->changeset);
                StatusWriter::forceWrite("R: {$sniff}:{$line} rolled back the changeset ({$numChanges} changes)", 2);
                StatusWriter::forceWrite('=> Changeset rolled back', 1);
            }
            $this->changeset = [];
        }
    }
    /**
     * Replace the entire contents of a token.
     *
     * @param int    $stackPtr The position of the token in the token stack.
     * @param string $content  The new content of the token.
     *
     * @return bool If the change was accepted.
     */
    public function replaceToken(int $stackPtr, string $content)
    {
        if ($this->inConflict === \true) {
            return \false;
        }
        if ($this->inChangeset === \false && isset($this->fixedTokens[$stackPtr]) === \true) {
            $depth = 1;
            if (empty($this->changeset) === \false) {
                $depth = 2;
            }
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::forceWrite("* token {$stackPtr} has already been modified, skipping *", $depth);
            }
            return \false;
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $bt = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
            if ($bt[1]['class'] === self::class) {
                $sniff = $bt[2]['class'];
                $line = $bt[1]['line'];
            } else {
                $sniff = $bt[1]['class'];
                $line = $bt[0]['line'];
            }
            $sniff = $this->getSniffCodeForDebug($sniff);
            $tokens = $this->currentFile->getTokens();
            $type = $tokens[$stackPtr]['type'];
            $tokenLine = $tokens[$stackPtr]['line'];
            $oldContent = Common::prepareForOutput($this->tokens[$stackPtr]);
            $newContent = Common::prepareForOutput($content);
            if (\trim($this->tokens[$stackPtr]) === '' && isset($this->tokens[$stackPtr + 1]) === \true) {
                // Add some context for whitespace only changes.
                $append = Common::prepareForOutput($this->tokens[$stackPtr + 1]);
                $oldContent .= $append;
                $newContent .= $append;
            }
        }
        if ($this->inChangeset === \true) {
            $this->changeset[$stackPtr] = $content;
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::forceWrite("Q: {$sniff}:{$line} replaced token {$stackPtr} ({$type} on line {$tokenLine}) \"{$oldContent}\" => \"{$newContent}\"", 2);
            }
            return \true;
        }
        if (isset($this->oldTokenValues[$stackPtr]) === \false) {
            $this->oldTokenValues[$stackPtr] = ['curr' => $content, 'prev' => $this->tokens[$stackPtr], 'loop' => $this->loops];
        } else {
            if ($this->oldTokenValues[$stackPtr]['prev'] === $content && $this->oldTokenValues[$stackPtr]['loop'] === $this->loops - 1) {
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $depth = 1;
                    if (empty($this->changeset) === \false) {
                        $depth = 2;
                    }
                    $loop = $this->oldTokenValues[$stackPtr]['loop'];
                    StatusWriter::forceWrite("**** {$sniff}:{$line} has possible conflict with another sniff on loop {$loop}; caused by the following change ****", $depth);
                    StatusWriter::forceWrite("**** replaced token {$stackPtr} ({$type} on line {$tokenLine}) \"{$oldContent}\" => \"{$newContent}\" ****", $depth);
                }
                if ($this->oldTokenValues[$stackPtr]['loop'] >= $this->loops - 1) {
                    $this->inConflict = \true;
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::forceWrite('**** ignoring all changes until next loop ****', $depth);
                    }
                }
                return \false;
            }
            $this->oldTokenValues[$stackPtr]['prev'] = $this->oldTokenValues[$stackPtr]['curr'];
            $this->oldTokenValues[$stackPtr]['curr'] = $content;
            $this->oldTokenValues[$stackPtr]['loop'] = $this->loops;
        }
        $this->fixedTokens[$stackPtr] = $this->tokens[$stackPtr];
        $this->tokens[$stackPtr] = $content;
        $this->numFixes++;
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $statusMessage = "{$sniff}:{$line} replaced token {$stackPtr} ({$type} on line {$tokenLine}) \"{$oldContent}\" => \"{$newContent}\"";
            $depth = 1;
            if (empty($this->changeset) === \false) {
                $statusMessage = 'A: ' . $statusMessage;
                $depth = 2;
            }
            StatusWriter::forceWrite($statusMessage, $depth);
        }
        return \true;
    }
    /**
     * Reverts the previous fix made to a token.
     *
     * @param int $stackPtr The position of the token in the token stack.
     *
     * @return bool If a change was reverted.
     */
    public function revertToken(int $stackPtr)
    {
        if (isset($this->fixedTokens[$stackPtr]) === \false) {
            return \false;
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $bt = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
            if ($bt[1]['class'] === self::class) {
                $sniff = $bt[2]['class'];
                $line = $bt[1]['line'];
            } else {
                $sniff = $bt[1]['class'];
                $line = $bt[0]['line'];
            }
            $sniff = $this->getSniffCodeForDebug($sniff);
            $tokens = $this->currentFile->getTokens();
            $type = $tokens[$stackPtr]['type'];
            $tokenLine = $tokens[$stackPtr]['line'];
            $oldContent = Common::prepareForOutput($this->tokens[$stackPtr]);
            $newContent = Common::prepareForOutput($this->fixedTokens[$stackPtr]);
            if (\trim($this->tokens[$stackPtr]) === '' && isset($tokens[$stackPtr + 1]) === \true) {
                // Add some context for whitespace only changes.
                $append = Common::prepareForOutput($this->tokens[$stackPtr + 1]);
                $oldContent .= $append;
                $newContent .= $append;
            }
        }
        $this->tokens[$stackPtr] = $this->fixedTokens[$stackPtr];
        unset($this->fixedTokens[$stackPtr]);
        $this->numFixes--;
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $statusMessage = "{$sniff}:{$line} reverted token {$stackPtr} ({$type} on line {$tokenLine}) \"{$oldContent}\" => \"{$newContent}\"";
            $depth = 1;
            if (empty($this->changeset) === \false) {
                $statusMessage = 'R: ' . $statusMessage;
                $depth = 2;
            }
            StatusWriter::forceWrite($statusMessage, $depth);
        }
        return \true;
    }
    /**
     * Replace the content of a token with a part of its current content.
     *
     * @param int      $stackPtr The position of the token in the token stack.
     * @param int      $start    The first character to keep.
     * @param int|null $length   The number of characters to keep. If NULL, the content of
     *                           the token from $start to the end of the content is kept.
     *
     * @return bool If the change was accepted.
     */
    public function substrToken(int $stackPtr, int $start, ?int $length = null)
    {
        $current = $this->getTokenContent($stackPtr);
        if ($length === null) {
            $newContent = (string) \substr($current, $start);
        } else {
            $newContent = (string) \substr($current, $start, $length);
        }
        return $this->replaceToken($stackPtr, $newContent);
    }
    /**
     * Adds a newline to end of a token's content.
     *
     * @param int $stackPtr The position of the token in the token stack.
     *
     * @return bool If the change was accepted.
     */
    public function addNewline(int $stackPtr)
    {
        $current = $this->getTokenContent($stackPtr);
        return $this->replaceToken($stackPtr, $current . $this->currentFile->eolChar);
    }
    /**
     * Adds a newline to the start of a token's content.
     *
     * @param int $stackPtr The position of the token in the token stack.
     *
     * @return bool If the change was accepted.
     */
    public function addNewlineBefore(int $stackPtr)
    {
        $current = $this->getTokenContent($stackPtr);
        return $this->replaceToken($stackPtr, $this->currentFile->eolChar . $current);
    }
    /**
     * Adds content to the end of a token's current content.
     *
     * @param int    $stackPtr The position of the token in the token stack.
     * @param string $content  The content to add.
     *
     * @return bool If the change was accepted.
     */
    public function addContent(int $stackPtr, string $content)
    {
        $current = $this->getTokenContent($stackPtr);
        return $this->replaceToken($stackPtr, $current . $content);
    }
    /**
     * Adds content to the start of a token's current content.
     *
     * @param int    $stackPtr The position of the token in the token stack.
     * @param string $content  The content to add.
     *
     * @return bool If the change was accepted.
     */
    public function addContentBefore(int $stackPtr, string $content)
    {
        $current = $this->getTokenContent($stackPtr);
        return $this->replaceToken($stackPtr, $content . $current);
    }
    /**
     * Adjust the indent of a code block.
     *
     * @param int $start  The position of the token in the token stack
     *                    to start adjusting the indent from.
     * @param int $end    The position of the token in the token stack
     *                    to end adjusting the indent.
     * @param int $change The number of spaces to adjust the indent by
     *                    (positive or negative).
     *
     * @return void
     */
    public function changeCodeBlockIndent(int $start, int $end, int $change)
    {
        $tokens = $this->currentFile->getTokens();
        $baseIndent = '';
        if ($change > 0) {
            $baseIndent = \str_repeat(' ', $change);
        }
        $useChangeset = \false;
        if ($this->inChangeset === \false) {
            $this->beginChangeset();
            $useChangeset = \true;
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($tokens[$i]['column'] !== 1 || $tokens[$i + 1]['line'] !== $tokens[$i]['line']) {
                continue;
            }
            $length = 0;
            if ($tokens[$i]['code'] === \T_WHITESPACE || $tokens[$i]['code'] === \T_DOC_COMMENT_WHITESPACE) {
                $length = $tokens[$i]['length'];
                $padding = $length + $change;
                if ($padding > 0) {
                    $padding = \str_repeat(' ', $padding);
                } else {
                    $padding = '';
                }
                $newContent = $padding . \ltrim($tokens[$i]['content']);
            } else {
                $newContent = $baseIndent . $tokens[$i]['content'];
            }
            $this->replaceToken($i, $newContent);
        }
        if ($useChangeset === \true) {
            $this->endChangeset();
        }
    }
    /**
     * Get the sniff code for the current sniff or the class name if the passed class is not a sniff.
     *
     * @param string $className Class name.
     *
     * @return string
     */
    private function getSniffCodeForDebug(string $className)
    {
        try {
            $sniffCode = Common::getSniffCode($className);
            return $sniffCode;
        } catch (InvalidArgumentException $e) {
            // Sniff code could not be determined. This may be an abstract sniff class or a helper class.
            return $className;
        }
    }
}
