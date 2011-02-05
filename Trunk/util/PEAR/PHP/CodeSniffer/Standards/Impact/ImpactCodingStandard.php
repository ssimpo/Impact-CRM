<?php
/**
 * PEAR Coding Standard.
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
 
if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception(
        'Class PHP_CodeSniffer_Standards_CodingStandard not found'
    );
}

/**
 * PEAR Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Stephen Simpson <me@simpo.org>
 * @copyright 2010 ImpactCRM
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL
 * @version   Release: 1.2.2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PHP_CodeSniffer_Standards_Impact_ImpactCodingStandard
    extends PHP_CodeSniffer_Standards_CodingStandard
{
    
    /**
     * Return a list of external sniffs to include with this standard.
     *
     * The Impact standard uses some generic sniffs.
     *
     * @return array
     */
    public function getIncludedSniffs()
    {
        return array();
    }
}
?>
