<?php
/**
 * Impact_Sniffs_NamingConventions_NamingSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: UpperCaseConstantNameSniff.php 291908 2009-12-09 03:56:09Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Impact_Sniffs_NamingConventions_NameingSniff.
 *
 * Ensures that functions and classes are named correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Impact_Sniffs_NamingConventions_NamingSniff implements PHP_CodeSniffer_Sniff
{
    private $className;
    
    protected $magicMethods = array(
        '__construct','__destruct','__call','__callStatic',
        '__get','__set','__isset','__unset',
        '__sleep','__wakeup','__toString','__set_state',
        '__clone',
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_CLASS, T_INTERFACE, T_FUNCTION);

    }//end register()


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
        $type = $tokens[$stackPtr]['type'];
        $pointer = $stackPtr;
        
        if ($type == 'T_FUNCTION') {
            
            $pointer++;
            if ($tokens[$pointer]['type'] == 'T_WHITESPACE') {
                $pointer++;
            }
            if ($tokens[$pointer]['type'] != 'T_STRING') {
                $error = 'Function not setup correctly';
                $phpcsFile->addError($error, $stackPtr);
                return;
            }
            
            $functionName = $tokens[$pointer]['content'];
            if (strtolower($functionName) != $functionName) {
                $caseError = false;
                //Special case for Unit Test, which need the function setUp()
                if (strlen($this->className) > 5) {
                    if (substr($this->className,0,5) != 'Test_') {
                        $caseError = true;
                    } elseif ($functionName != 'setUp') {
                        $caseError = true;
                    }
                } else {
                    $caseError = true;
                }
                if ($caseError) {
                    $error = 'Function name '.$functionName.'() is not in lowercase';
                    $phpcsFile->addError($error, $stackPtr);
                    return; 
                }
                
            }
            
            $scopeModifier = $phpcsFile->findPrevious(
                PHP_CodeSniffer_Tokens::$scopeModifiers, $stackPtr
            );
            $functionLine = $tokens[$pointer]['line'];
            $scopeLine = $tokens[$scopeModifier]['line'];
            
            if ($functionLine == $scopeLine) {
                $scopeType = $tokens[$scopeModifier]['content'];
                $firstLetter = substr($functionName, 0, 1);
                
                if (in_array($functionName, $this->magicMethods) === false) {
                    if (($firstLetter != '_') && ($scopeType == 'private')) {
                        $error = 'Private function '.$functionName.
                            '() should start with an underscore';
                        $phpcsFile->addError($error, $stackPtr);
                        return;
                    }
                    if (($firstLetter == '_') && ($scopeType == 'public')) {
                        $error = 'Public function '.$functionName.
                            '() should not start with an underscore';
                        $phpcsFile->addError($error, $stackPtr);
                        return;
                    }
                }
            } 
        } else {
            $pointer++;
            if ($tokens[$pointer]['type'] == 'T_WHITESPACE') {
                $pointer++;
            }
            $className = $tokens[$pointer]['content'];
            if ($tokens[$pointer]['type'] != 'T_STRING') {
                $error = 'Class/Interface: '.$className.'{} not setup correctly';
                $phpcsFile->addError($error, $stackPtr);
                return;
            }
            
            $classNameError = false;
            $parts = explode('_', $className);
            foreach ($parts as $part) {
                if (strlen($part) > 1) {
                    $firstLetter = substr($part, 0, 1);
                    if (strtolower($firstLetter) == $firstLetter) {
                        $classNameError = true;
                    }
                } else {
                    if (strtolower($part) == $part) {
                        $classNameError = true;
                    }
                }
            }
            if ($classNameError) {
                $error = 'Class/Interface: '.$className.
                    '{} name incorrectly, parts should be seperated by an underscore'.
                    ' and each word should begin with a capital letter';
                $phpcsFile->addError($error, $stackPtr);
                return;
            }
            $this->className = $className;
        }

    }//end process()


}//end class

?>
