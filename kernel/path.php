<?php
/*
 * -File        path.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2005, Nexista
 * -Author      joshua savage
 */

/**
 * @package Nexista
 * @author Joshua Savage
 */

/**
 * This class Nexista_provides functionality to resolve paths with different protocols
 * such as flow, php, get etc... for use in xml files such as actions, validators
 * queries, etc..
 * In many situations, such as <i>src</i> attributes of sitmap tags, query parameters, 
 * etc.... a protocol can be specified along with the desired variable in order 
 * to access data such as Flow, $_REQUEST, URIs, etc.... 
 * Path provides a standardized manner in which to access this data. For example, 
 * a <i>map:xsl</i> tag will have a <i>src</i> attribute defining the location 
 * of the XSL file. The defaut protocol in this case would be <i>string</i> so 
 * that, if no protocol is specfified, the value will be handled as a string. But 
 * other protocols can be used to resolve this value such as <i>flow://some/var</i> 
 * or <i>php:some_php_eval_expression</i> or <i>get://some/get/var</i>, etc.. Not 
 * all situations/tags accept multi-protocols. Please refer to the tag references 
 * for details.
 *
 *  Currently available protocols are:
 * -<b>get</b> - $_GET variables
 * -<b>post</b> - $_POST variables
 * -<b>globals</b> - $GLOBALS variables
 * -<b>request</b> - $_REQUEST variables
 * -<b>session</b> - $_SESSION variables
 * -<b>files</b> - $_FILES variables
 * -<b>server</b> - $_SERVER variables
 * -<b>cookie</b> - $_COOKIE variables
 * -<b>registry</b> - Registry data - a special subest of the session handling.
 * -<b>flow</b> - Flow variables
 * -<b>php</b> - PHP evaluation - Note that a <i>return</i> statement is automatically 
  appended as well as a final semicolon.
 * -<b>string</b> - Plain string
 * 
 * In the case of XML files such as the sitemap, queries, etc... a path variable 
 * is simply specified as a standard URI such as:
 * <code><!-- sample sitemap tag -->
 * <map:xsl src="flow://my/xsl"/>
 *
 * <!-- sample query parameter -->
 * <param name="currentTime" default="php:time()"/></code>
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
    static public function get($path, $defaultProtocol = 'string')
    {
   
        //TODO - we can probably have multiple URIs in a row. make loop for that.
        $request = explode('|', $path);
        
        $result = null;
        for($i = 0; $i < count($request); $i++)
        {
       
            $array = explode( ':', $request[$i], 2 );
        
            if(count($array) > 1)
            {
                $protocol = $array[0];
                $path = $array[1];
            }
            else
            {
                $protocol = $defaultProtocol;
                $path = $request[$i];
            }
       
            //match protocol
            switch($protocol)
            {
            
                //_GET 
                case 'get':
                     
                    $result = Nexista_Path::interpretPath($_GET, $path);
                    break;
                        
                //_POST 
                case 'post':
                    
                    $result = Nexista_Path::interpretPath($_POST, $path);
                    
                    break;
                        
                //_REQUEST
                case 'request':
                    
                    $result = Nexista_Path::interpretPath($_REQUEST, $path);
                    
                    break;
                        
                //_SESSIONS
                case 'session':
                        
                    $result = Nexista_Path::interpretPath($_SESSION, $path);
                    
                    break;
                    
                //_FILES
                case 'files':
                    
                    $result = Nexista_Path::interpretPath($_FILES, $path);
                    break;
                    
                //GLOBALS
                case 'globals':
                                   
                    $result = Nexista_Path::interpretPath($GLOBALS, $path);
                   
                    break;
                        
                //_SERVER
                case 'server':
                    
                    $result = Nexista_Path::interpretPath($_SERVER, $path);
                    break;
                    
                //_COOKIES
                case 'cookie':
                    
                    $result = Nexista_Path::interpretPath($_COOKIE, $path);
                    break;
                    
                //internal registry  
                case 'registry':
                    
                    die('registry resolver not done');
                    break;
                    
                //flow   
                case 'flow':
               
                    $result = Nexista_Flow::getByPath($path);
                    break;
                        
                //eval a php expression
                case 'php':
              
                    //escape double quotes
                    //$path = preg_replace('~"~', '/"', $path); //what was this for?

				   $expression = "return ".$path.";";
                   $result = @eval($expression);
                    break;
                
                        
                //probably a plain var or a file,url protocol (file://, http://, etc...)
                case 'string':
                default:
                
                    $result = Nexista_Path::parseInlineFlow($path);
                    break;    
                
                
            }
            
           
            //if we have a value, break out and return that
            if(!is_null($result))  
            {         
                return $result;       
            } else { 
				
				return false;
			}
        }
       
        return;
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
        $string = preg_replace_callback('~{(.*)}~U',create_function('$matches', 'return Flow::getByPath($matches[1]);'),$string);
        return $string;
    }
    
    
    /**
     * Interprets a slash separated path as an associative array
     *
     * This method accepts an array and a slash delimited path (this/is/it),
     * parses it for inline flow expressions and returns the corresponding
     * associative array value. (ex: $_GET['this']['is']['it'])
     *
     * @param   array       array to iterate through
     * @param   string      path to interpret
     * @return  mixed       value of array or null if not found
     */

    private function interpretPath($data, $request)
    {
         //inline flow expressions   
        //$string = Nexista_Path::parseInlineFlow($string);
       
        $request = explode('/', trim($request, '/'));

        for($i = 0; $i < count($request); $i++)
        {
            if(isset($data[$request[$i]]))
            {
                $data = $data[$request[$i]];

            }
            else
            {
                return null;
            }
        }

        if(!empty($data))
            return $data;
        return null;

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