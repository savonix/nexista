<?php
/*
 * -File        path.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Albert Lash
 * -Author      joshua savage
 */

/**
 * @package Nexista
 * @author Joshua Savage
 */

/**
 * This class provides 
 *
 * -<b>string</b> - Plain string
 *
 *
 * @package     Nexista
 */

class Nexista_Path
{

    /**
     * Hold an instance of the class
     */

    static private $instance;

    /**
     * Returns a string based on given protocol://path 
     *
     * @param   string      path to resolve/render
     * @param   string      (optional) default protocol if none given
     * @return  string      value of variable
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
        if(!is_null($result))
        {
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
     * @param       string      path to analyze, returns by ref
     * @return      string      path with resolved inline flow expressions
     */

    static public function parseInlineFlow($string)
    {
        //replace bracketed flow expressions
        $string = preg_replace_callback('~{(.*)}~U',create_function('$matches', 'return Nexista_Flow::getByPath($matches[1]);'),$string);
        return $string;
    }


    /**
     * Returns a class Nexista_singleton.
     *
     * @return  object class Nexista_singleton instance
     */

    static public function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }
}

?>