<?php
/*
 * -File        pathbuilder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage
 * -Author      Albert Lash
 */

/**
 * @package Nexista
 * @author Joshua Savage
 * @author Albert Lash
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
 * @package     Nexista
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
     * @access    public
     * @param     string path to resolve/render
     * @param     string optional default protocol if none given
     * @param     integer joining style constant
     */
    public function get($path, $protocol = 'string', $joinStyle = JOIN_NONE)
    {
        /*
        //TODO - we can probably have multiple URIs in a row. make loop for that.
        $array = preg_split( "~:~", $path );

        if(count($array) > 1)
        {
            $protocol = $array[0];
            $path = $array[1];
        }
        //match protocol
        switch($protocol)
        {
            //_GET 
            case 'get':
                $code[] = '$_GET'.$this->transformPath($path, $joinStyle);
                break;

            //_POST
            case 'post':
                $code[] = '$_POST'.$this->transformPath($path, $joinStyle);
                break;

            //_REQUEST
            case 'request':
                $code[] = '$_REQUEST'.$this->transformPath($path, $joinStyle);
                break;

            //_SESSIONS
            case 'session':
                $code[] = '$_SESSION'.$this->transformPath($path, $joinStyle);
                break;

            //_FILES
            case 'files':
                $code[] = '$_FILES'.$this->transformPath($path, $joinStyle);
                break;

            //GLOBALS
            case 'globals':
                $code[] = '$GLOBALS'.$this->transformPath($path, $joinStyle);
                break;

            //_SERVER
            case 'server':
                $code[] = '$_SERVER'.$this->transformPath($path, $joinStyle);
                break;

            //_COOKIE
            case 'cookie':
                $code[] = '$_COOKIE'.$this->transformPath($path, $joinStyle);
                break;

            //flow
            case 'flow':
                $code[] = "Nexista_Flow::getByPath('".$path."')";
                break;

            //probably a plain var or a file,url protocol (file://, http://, etc...)
            case 'string':
            default:

                $code[] = $this->parseInlineFlow($path, $joinStyle);
                break;
        }
        */
        $code[] = $this->parseInlineFlow($path, $joinStyle);
        return implode(NX_BUILDER_LINEBREAK, $code);
    }


    /**
     * Resolves inline flow vars.
     *
     * This method will look for curly bracketed values in a string
     * and return a flow expression.
     *
     * @param   string      path to analyze
     * @param   constant    string joining style for {flow} inline expressions
     * @return  string      path with inline flow expressions
     */

    public function parseInlineFlow($string, $joinStyle=JOIN_NONE)
    {

        //first quote/join brackets, ending quotes, etc...
        $string = preg_replace(array('~(?<!^|}){~','~}(?!$|{)~', '~}{~', '~^[^{]~', '~[^}]$~'), array("'.{", "}.'", '}.{', "'$0", "$0'"), $string);

        //replace bracketed flow expressions
        $string = preg_replace('~{(.*)}~U',
        'Nexista_Flow::getByPath("${1}")', $string);

        return $string;

    }
}

?>