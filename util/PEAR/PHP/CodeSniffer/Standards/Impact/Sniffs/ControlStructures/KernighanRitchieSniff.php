<?php
/**
 * Impact_Sniffs_KernighanRitchieSniff
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Stephen Simpson <me@simpo.org>
 * @copyright 2010 ImpactCRM
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Impact_Sniffs_ControlStructures_KernighanRitchieSniff.
 *
 * If an assignment goes over two lines, ensure the equal sign is indented.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Stephen Simpson <me@simpo.org>
 * @copyright 2010 ImpactCRM
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Impact_Sniffs_ControlStructures_KernighanRitchieSniff implements PHP_CodeSniffer_Sniff
{
    
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * The token we are testing are code blocks (eg. class {} or for {}
     * or while {}).
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_CLASS, T_INTERFACE, T_FUNCTION, T_IF, T_SWITCH,
            T_WHILE, T_ELSE, T_ELSEIF, T_FOR, T_FOREACH,
            T_DO, T_TRY, T_CATCH
        );
    }
    
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                          in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }
        
        $scopeOpener = $tokens[$stackPtr]['scope_opener'];
        $scopeCloser = $tokens[$stackPtr]['scope_closer'];
        $scopeCondition = $tokens[$stackPtr]['scope_condition'];
        $type = $tokens[$stackPtr]['type'];

        // The end of the function occurs at the end of the argument list. Its
        // like this because some people like to break long function declarations
        // over multiple lines.
        
        $lineDifference = 0;
        if (isset($tokens[$stackPtr]['parenthesis_closer']) === true) {
            $parenthesisCloser = $tokens[$stackPtr]['parenthesis_closer'];
            $parenthesisOpener = $tokens[$stackPtr]['parenthesis_opener'];
            $functionLine = $tokens[$parenthesisCloser]['line'];
            $braceLine = $tokens[$scopeOpener]['line'];
            
            // Checks that the closing parenthesis and the opening brace are
            // separated by a whitespace character.
            $closerColumn = $tokens[$parenthesisCloser]['column'];
            $braceColumn  = $tokens[$scopeOpener]['column'];

            $columnDifference = ($braceColumn - $closerColumn);

            if ($columnDifference !== 2) {
                $error = 'Expected 1 space between the closing parenthesis and the opening brace; found '.($columnDifference - 1).'.';
                $phpcsFile->addError($error, $scopeOpener);
                return;
            }

            // Check that a tab was not used instead of a space.
            $spaceTokenPtr = ($parenthesisCloser + 1);
            $spaceContent  = $tokens[$spaceTokenPtr]['content'];
            if ($spaceContent !== ' ') {
                $error = 'Expected a single space character between closing parenthesis and opening brace; found "'.$spaceContent.'".';
                $phpcsFile->addError($error, $scopeOpener);
                return;
            }
            
            //Check that there is no space between the funtion and the opening
            // parenthesis and a single space for s statement - Eg.
            // functionname(parm) not functionname (parm);
            // and while ($i > 1) not while($i > 1)
            $openerColumn = $tokens[$parenthesisOpener]['column'];
            $previousTokenPtr = ($parenthesisOpener - 1);
            $previousTokenType = $tokens[$previousTokenPtr]['type'];
            $previousTokenContent = $tokens[$previousTokenPtr]['content'];
            if ($type == 'T_FUNCTION') {
                if ($previousTokenType != 'T_STRING') {
                    $error = 'Gap between function name and parenthesis';
                    $phpcsFile->addError($error, $scopeOpener);
                    return;
                }
            } else {
                if ($previousTokenType != 'T_WHITESPACE') {
                    $error = 'No gap between control statement and parenthesis';
                    $phpcsFile->addError($error, $scopeOpener);
                    return;
                } elseif ($previousTokenContent !== ' ') {
                    $error = 'Expected a single space character between statement and opening parenthesis; found "'.$previousTokenContent.'".';
                    $phpcsFile->addError($error, $scopeOpener);
                    return;
                }
            }
        } else {
            $functionLine = $tokens[$scopeCondition]['line'];
            $braceLine = $tokens[$scopeOpener]['line'];
        }
        
        $lineDifference = ($braceLine - $functionLine);
        if ($lineDifference > 0) {
            $error = 'Opening brace should be on the same line as the declaration';
            $phpcsFile->addError($error, $scopeOpener);
            return;
        }
        
        // Check that the opening/closing curley-brackets are in the same column.
        $openingColumn = 0;
        $closingColumn = 0;
        if ($type != 'T_FUNCTION') {
            $openingColumn = $tokens[$scopeCondition]['column'];
            $closingColumn = $tokens[$scopeCloser]['column'];
            
            if (($type == 'T_ELSE') || ($type == 'T_ELSEIF') || ($type == 'T_CATCH')) {
                $openingColumn -= 2;
            }
        } else {
            $scopeModifier = $phpcsFile->findPrevious(
                PHP_CodeSniffer_Tokens::$scopeModifiers, $stackPtr
            );
            $functionLine = $tokens[$stackPtr]['line'];
            $scopeLine = $tokens[$scopeModifier]['line'];
            
            if ($functionLine == $scopeLine) {
                $closingColumn = $tokens[$scopeCloser]['column'];
                $openingColumn = $tokens[$scopeModifier]['column'];
            }
        }
        if ($openingColumn != $closingColumn) {
            $error = 'Closing brace is not in the same column as the opening statement';
            $phpcsFile->addError($error, $scopeCloser);
            return;
        }
        
    }
}
?>