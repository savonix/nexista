<?php
/**
 * -File        pathbuilder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */


/**
 * Links path building elements with single quotes
 *
 * @name    JOIN_SINGLE_QUOTE
 */
define('JOIN_SINGLE_QUOTE', 1);

/**
 * Links path building elements with double quotes
 *
 * @name    JOIN_DOUBLE_QUOTE
 */
define('JOIN_DOUBLE_QUOTE', 2);

/**
 * Links path building elements without quotes
 *
 * @name    JOIN_NONE
 */
define('JOIN_NONE', 3);

/**
 * This class Nexista_provides functionality to create strings that will resolve
 * into functional paths/value at runtime. This is the same process as the 
 * Nexista_Path class, however it does not evaluate the expression but rather
 * returns a string that is inserted in the compiled files that will resolved
 * at runtime.
 * See Nexista_Path class for information on protocols handled.
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */


class Nexista_PathBuilder
{

    /**
     * String concatenation style for inline flow elements
     *
     * When a path containes an inline flow expression such as:
     *     "http://path{inline/flow}/here"
     * the final rendering must join a string with the resolved flow expression:
     *     "http://path".Flow::getVar('inline/flow')."/here"
     * The quote style depends on the calling expression so it must be determined
     * when calling the Resolver::get function for applicable situtations.
     *
     * @var     array
     */

    public $joins = array(
        JOIN_SINGLE_QUOTE => "'",
        JOIN_DOUBLE_QUOTE => '"',
        JOIN_NONE         => '');


    /**
     * Returns a string based on given protocol://path
     * This function probably exists as a faster way to access superglobal
     * variables as opposed to traversing the XML document.
     *
     * @param string  $path      path to resolve/render
     * @param string  $protocol  optional default protocol if none given
     * @param integer $joinStyle joining style constant
     *
     * @return string code
     */
    public function get($path, $protocol = 'string', $joinStyle = JOIN_NONE)
    {
        switch($protocol) {
        case 'flow':
            $code[] = "Nexista_Flow::getByPath('".$path."')";
            break;
        
        default:
            $code[] = $this->parseInlineFlow($path, $joinStyle);
        }
        return implode(NX_BUILDER_LINEBREAK, $code);
    }


    /**
     * Resolves inline flow vars.
     *
     * This method will look for curly bracketed values in a string
     * and return a flow expression.
     *
     * @param string $string    path to analyze
     * @param string $joinStyle joining style for {flow} inline expressions
     *
     * @return string path with inline flow expressions
     */

    public function parseInlineFlow($string, $joinStyle=JOIN_NONE)
    {

        //first quote/join brackets, ending quotes, etc...
        $string = preg_replace(array(
                                '~(?<!^|}){~',
                                '~}(?!$|{)~',
                                '~}{~',
                                '~^[^{]~',
                                '~[^}]$~'), 
            array("'.{", "}.'", '}.{', "'$0", "$0'"), $string);

        //replace bracketed flow expressions
        $string = preg_replace('~{(.*)}~U',
        'Nexista_Flow::getByPath("${1}")', $string);

        return $string;

    }
}

?>