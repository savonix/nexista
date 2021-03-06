<?php
/**
 * -File        path.php
 * -Copyright   Savonix Corporation
 * -Author      Albert Lash
 * -Author      joshua savage
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
 * This class provides:
 *
 * -<b>string</b> - Plain string
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

class Nexista_Path
{

    /**
     * Returns a string based on given protocol://path 
     *
     * @param string $path     path to resolve/render
     * @param string $protocol (optional) default protocol if none given
     *
     * @return string value of variable
     */
    static public function get($path, $protocol = 'string')
    {
        switch($protocol) {
        case 'flow':
            $result = Nexista_Flow::getByPath($path);
            break;

        default:
            $result = Nexista_Path::parseInlineFlow($path);
        }

        if (!is_null($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Resolves inline flow vars.
     *
     * This method will look for curly bracketed values in a string
     * and return a flow expression.
     *
     * @param string $string path to analyze, returns by ref
     * 
     * @return string path with resolved inline flow expressions
     */

    static public function parseInlineFlow($string)
    {
        //replace bracketed flow expressions
        $string = preg_replace_callback('~{(.*)}~U', create_function('$matches', 
            'return Nexista_Flow::getByPath($matches[1]);'), $string);
        return $string;
    }


}

?>