<?php
/*
 * -File        flow.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage
 * -Author      Albert Lash
 */

/**
 * @package Nexista
 * @author Joshua Savage
 */

/**
 * This class Nexista_handles I/O between sitemap nodes (files) and will output 
 * an xml tree at the end if requested by the XSLT handler.
 *
 * One of the core features of Nexista is the Flow concept. Flow represents the 
 * wiring so to speak of the application. It controls all data i/o from scripts, 
 * databases, get/post, etc... And allows for a unified interface to all these 
 * variables as well as permits "smart" access to the data. Flow data is eventually 
 * used to generate XML which can be used in subsequent XSL transformations. 
 *
 * Flow data handling is transparent to the developer. The only necessary thing 
 * to understand is how to call Flow variables and what data can be accessed in 
 * this manner.
 *
 * The flow class Nexista_is an extension of the {@link "PHP5 DOM API" href="http://www.php.net/manual/en/ref.dom.php} 
 * and thus provides all the standard XML functionality as well as a few additional 
 * methods. 
 *
 * At startup, Flow is initialized with a number of special variables. This data 
 * is user writeable so actions and other modifiers can be used. By default, Flow 
 * imports these variables as they are. A user specified handler function can be 
 * called to control the import process - see Flow::registerImportHandler in API 
 * docs.
 *
 * <b>_get</b> - Initialized with a copy of $_GET data. All incoming GET data 
 * will be found there (ex: nx_get/nid would represent the current requested gate 
 * - $_GET['nid']).
 * <b>_post </b>- Initialized with a copy of $_POST data. All incoming POST data 
 * will be found there (ex: nx_post/submit could be used to see if a form has been 
 * submitted - $_POST['submit']). This data is user writeable so actions and other 
 * modifiers can be used.
 * <b>_files</b> - Initialized with a copy of $_FILES data.
 * <b>_globals</b> - Initialized with a copy of $GLOBALS data.
 * <b>_session </b>- Initialized with a copy of $_SESSION data.
 *
 * Flow is user writeable and developers are free to organize their data as they 
 * see fit. Certain actions such as queries and validator will write their data 
 * to flow based on their name. Ex: a select query with the name 'article' will 
 * return its data as:
 * <code><article>
 *      <title>Article One</title>
 *      <data>The text goes here</data> 
 * </article>
 * <article>
 *      <title>Article Two</title>
 *      <data>some stuff here as well</data> 
 * </article>
 *      ...and so on for each row found </code>
 *
 * @package     Nexista
 */

class Nexista_Flow
{


    /**
     * Hold an instance of the class
     *
     * @var     object
     */

    static private $instance;


    /**
     * Flow DOM document object
     *
     * @var     DOMDocument
     */

    public $flowDocument;


    /**
     * Flow DOM document root node
     *
     * @var     DOMElement
     */

    public $root;

    /**
     * Array type
     *
     * @var     string
     */
     
    public $array_type;
    
    
    /**
     * Flow init action handler
     *
     * @var     mixed
     */
     
    static private $importHandler;


    /**
     * Main xml output data container.
     *
     * stream data holder. Is an xml string that will be eventually
     * outputed to any xml receiving module such as a xsl transformer.
     * This is the only source of display content. Some modules such as
     * raw xml files, will output direclty to stream, while others, such as
     * script modules, will merge all their dynamic data at the end to stream
     * using write()
     *
     * @var     string
     */

     public $xmlStream;


    /**
     * Initialize flow with basic data (request,session)
     *
     */

    public function init()
    {
        //get config data
        $params = Nexista_Config::getSection('flow');

        //create a new DOM document and init with root
        $this->flowDocument = new DOMDocument("1.0", "UTF-8");
        //$this->flowDocument->preserveWhiteSpace=false;
        $this->flowDocument->strictErrorChecking = false;
        $this->flowDocument->formatOutput = false;
        $my_doc_root = $params['my_doc_root'];
        if(empty($my_doc_root)) {
            $my_doc_root = "__ROOT__";
        }
        $this->root = $this->flowDocument->createElement($my_doc_root);
        $this->root = $this->flowDocument->appendChild($this->root);

        for($i = 0; $i < strlen($params['request']); $i ++)
        {
            switch($params['request'][$i])
            {
                //add $_GET vars
                case 'G':

                    if(!is_null(self::$importHandler)) {
                        $ref = $this->add('_get',call_user_func(self::$importHandler, $_GET));
                    } else {
                        $ref = $this->add('_get', $_GET);
                    }
                    break;

                //add $_POST vars
                case 'P':

                    if(!is_null(self::$importHandler)) 
                        $ref = $this->add('_post',call_user_func(self::$importHandler, $_POST));
                    else
                        $ref = $this->add('_post', $_POST);
                    break;

                //add $_FILES vars
                case 'F':
                    if(!is_null(self::$importHandler))
                        $ref = $this->add('_files',call_user_func(self::$importHandler, $_FILES));
                    else
                        $ref = $this->add('_files', $_FILES);
                    break;

                //add $_SESSION vars
                case 'S':
                    if(!is_null(self::$importHandler))
                        $ref = $this->add('_session',call_user_func(self::$importHandler, $_SESSION));
                    else
                        $ref = $this->add('_session', $_SESSION);
                    break;

                //add server
                case 'V':
                    if(!is_null(self::$importHandler))
                        $ref = $this->add('_server',call_user_func(self::$importHandler, $_SERVER));
                    else
                        $ref = $this->add('_server', $_SERVER);
                    break;

                //add env
                case 'E':
                    if(!is_null(self::$importHandler))
                        $ref = $this->add('_env',call_user_func(self::$importHandler, $_ENV));
                    else
                        $ref = $this->add('_env', $_ENV);
                    break;

                //add globals
                case 'W':
                    if(!is_null(self::$importHandler)) 
                        $ref = $this->add('_globals',call_user_func(self::$importHandler, $GLOBALS));
                    else
                        $ref = $this->add('_globals', $GLOBALS);
                    break;
            }
        }
    }

    /**
     * Finds a flow var using an xpath query
     *
     * @param   string      xpath expression
     * @param   object      (optional) reference to parent node for relative searches
     * @return  object      DOMNodeList, empty if nothing found
     */

    static public function find($exp, $parent = null)
    {
        $flow = Nexista_Flow::singleton();
        $x = new DOMXPath($flow->flowDocument);

        if(is_null($parent))
            return @$x->query($exp);
        else
            return @$x->query($exp, $parent);

    }

    /**
     * Returns the value of a flow variable
     *
     * If the value of a variable is CDATA, it will return this. If the 
     * variable contains children nodes, it will return a recursive array
     *
     * @param   object      a valid DOMElement reference
     * @param   string      for single values: associative or numeric array?
     * @return  mixed       string or array of variable contents
     */

    static public function get($node,$array_type = null)
    {

        //see if a text node with no children
        if($node->childNodes->length == 1  AND
            get_class($node->childNodes->item(0)) == 'DOMText' AND
            !is_null($node->nodeValue))
        {
            if($array_type=="ASSOC") {
                $nodeName = $node->nodeName;
                $nodeValue = $node->nodeValue;
                $result[$nodeName] = $nodeValue;
                return $result;
            } else {
                return $node->nodeValue;
            }
        }
        //children - we loop and make array
        elseif($node->childNodes->length > 0)
        {
            $result = array();
            foreach($node->childNodes as $kid)
            {

                if($kid->childNodes->length == 1  AND 
                    get_class($kid->childNodes->item(0)) == 'DOMText' AND
                    !is_null($kid->nodeValue))
                {

                    $result[$kid->nodeName] = $kid->nodeValue;
                }
                else
                {
                    $result[$kid->nodeName] = Nexista_Flow::get($kid);
                }
            }
            return $result;
        }
        return null;
    }

    /**
     * Deletes a flow variable and all its content
     *
     *
     * @param   object      reference to flow var to delete
     */

    static public function delete($node)
    {
		$flow = Nexista_Flow::singleton();
		$listall = $flow->flowDocument->getElementsByTagname($node);
		$count = $listall->length;
		for ($i = 0; $i < $count; $i++) {
			$myparentnode = $listall->item(0)->parentNode;
			$myparentnode->removeChild($listall->item(0));
        }
	}


    /**
     * Returns the value of a flow variable(s) given an xpath query
     *
     * This is a wrapper for a joined find/get item query. 
     * If multiple items are found, it will only return the first one
     *
     * @param   string      an xpath query
     * @return  mixed       value of first found variable, or an array if multiple matches, 
     *                      or null if no matches
     */

    static public function getByPath($path,$array_type = null)
    {

        $flow = Nexista_Flow::singleton();
        $res = Nexista_Flow::find($path);
        if($res->length > 1)
        {
            $array = array();
            foreach($res as $r)
            {
                $array[] = $flow->get($r,$array_type);
            }
            return $array;
        }
        if($res->length == 1)
        {
            $string = $flow->get($res->item(0),$array_type);
            if(is_null($string) OR empty($string))
                return null;
            return $string;
        }
        else
            return null;
    }



    /**
     * Returns a class Nexista_singleton.
     *
     * @return  object      class Nexista_singleton instance
     */

    static public function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }


     /**
     * Outputs flow as xml
     *
     * @return  string      xml
     */

    public function outputXml($node)
    {
        return $this->flowDocument->saveXML($node);
    }
  
    /**
     * Creates and appends one or more children elements
     *
     * This method is a shortcut to the createElement, appendChild
     * sequence and can also accept associative arrays of any depth
     *
     * There are a few ways to call this method:
     * - Flow::add('name', 'value'); would result in a new child at the root level 
     * called 'name' with a value of 'value'.
     * - Flow::add(array('name1'=>'value1', 'name2'=>'value2')); would result in two
     * new children at root level (name1 and name2) with respective values
     * - Flow:add('name', array('blue','red')); would result in 2 new nodes at root
     * level of name 'name' with values of 'blue' and 'red' respectively.
     * - Flow::add('name', array('sub1'=> 'blue','sub2'=>'red')); would result in a 
     * new node of name 'name' at rool level, which contains 2 children (sub1 and sub2)
     * each with the values of blue and red respectively.
     * 
     * Note that multi level arrays can be used.
     * 
     * @param   mixed       a string for a single child or associative array of child->values
     * @param   mixed       (optional) if a string is passed with $node, this is the value. 
     *                      If an numerical array is passed, it represents the
     *                      multiple children of type $node.
     * @param   object      (optional) reference to a DOMElement child
     *                      if none is passed, root is assumed
     * @return  boolean     success. Usual fail reason is non valid XML names (numeric arrays)
     */

    static public function add($node, $value = null, $root = false)
    {
        $flow = Nexista_Flow::singleton();

        //where do we place this?
        if(!$root)
            $root = $flow->root;

        if(is_array($node))
        {

            foreach($node as $n=>$v)
            {
                if(is_numeric)
                    return false;

                $flow->add($n, $v, $root);
            }
        }
        else
        {
            if(is_array($value))
            {
                //numeric array? we replicate the node
                if(is_numeric(key($value)))
                {
                    foreach($value as $n=>$v)
                    {
                        $flow->add($node, $v,  $root);
                    }
                }
                //associative array? we add as child nodes
                else
                {
                    $e = $root->appendChild($flow->flowDocument->createElement($node)); 
                    foreach($value as $n=>$v)
                    {
                        if($n=="GLOBALS") {
                            continue;
                        }
                        $flow->add($n, $v, $e);
                    }

                }
            }
            elseif(!is_null($value))
            {
                $e = $root->appendChild($flow->flowDocument->createElement($node));
                $e->appendChild($flow->flowDocument->createTextNode($value));
            }
        }

        return true;
    }

    /**
     * Registers a function to be called on init
     *
     * This function will be called when Flow inits and imports
     * things such as the $_GET, $_POST datasets. This permits the developer
     * to perform actions before assignement to flow such as stripping tags, etc...
     * This method will be given an array and needs to return an array.
     *
     * @param  mixed        a function or an array of class=>method
     */

    static public function registerImportHandler($handler)
    {

        if(is_callable($handler))
            self::$importHandler = $handler;
        else
            Nexista_Error::init("Flow Import Handler is not callable!");
    }

}

?>