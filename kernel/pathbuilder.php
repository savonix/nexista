<?php
/*
 * -File        pathbuilder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2005, Nexista
 * -Author      joshua savage
 */

/**
 * @package Nexista
 * @author Joshua Savage
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
 * Path class, however it does not evaluate the expression but rather
 * returns a string that is inserted in the compiled files that will resolved
 * at runtime.
 * See Path class Nexista_for information on protocols handled.
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
     *
     * @access    public
     * @param     string path to resolve/render
     * @param     string optional default protocol if none given
     * @param     integer joining style constant  
     */
    public function get($path, $protocol = 'string', $joinStyle = JOIN_NONE)
    {
   
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
                  
            //internal registry  
            case 'registry':
                
                die('registry resolver not done');
                break;
                 
            //flow   
            case 'flow':
                
                $code[] = "Nexista_Flow::getByPath('".$path."')";
                break;
                    
            //eval a php expression
            case 'php':
              
                //escape double quotes
                $path = preg_replace('~"~', '/"', $path);
                $code[] = 'eval("return '.$path.';")';
                break;
              
            //evaluate a regular expression against nid 
            //NO PURPOSE HERE - kill?
            case 'regex':
                
                $code[] = 'preg_match("~^('.$path.')$~", $_GET["'.Config::get('./build/query').'"], $GLOBALS["match"])';
                break;
                    
            //probably a plain var or a file,url protocol (file://, http://, etc...)
            case 'string':
            default:
               
                $code[] = $this->parseInlineFlow($path, $joinStyle);
                break;
        }
        return implode(NX_BUILDER_LINEBREAK, $code);
    }
      
    
    /**
     * Transforms a slash path into an associative array string
     *
     * This method accepts a path such as /this/is/it and will return
     * an array string (i.e. ['this']['is']['it']) for later evaluation.
     *
     * @param   string      string to transform
     * @param   constant    string joining style for {flow} inline expressions
     * @return  string      array expression string 
     */
    
    public function transformPath($string, $joinStyle=JOIN_NONE)
    {    
        //inline flow expressions?    
        if(strpos($string, '{') !== false)
        {
           //do main replace          
           $string = "[".preg_replace_callback('~(\{[^{}]*\})|(/)|([^/{}]*)~', array ($this, 'transformPathCallback'),trim($string, '/'))."]";
           
           //have to deal with strings touching flow vars. need to join properly
           $search = array("~'Flow~", "~\)'~", '~\)Flow~');
           $replace = array("'.Flow", ").'", ').Flow');
          
           $string = preg_replace($search, $replace ,$string);
           
           return $string;
        }
        else
        {
            return "['".preg_replace('~/~', "']['",trim($string, '/'))."']";
        }
    }
    
      
    /**
     * Callback expression for Resolver::transformPath
     *
     * @param   array       match array 
     * @return  string      preg_replace value 
     */
    private function transformPathCallback($matches)
    {
       //print_r($matches);

        if(!empty($matches[3]))
        {
             return "'".$matches[3]."'";
        }
        elseif(!empty($matches[2]))
        {
            return '][';
        }
        elseif(!empty($matches[1]))
        {
            return $this->parseInlineFlow($matches[1]);
        }
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