<?php
/*
 * -File        query.handler.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage
 */
 
/**
 * This class is the query handler.
 * It uses an xml query and retrieves from or sends data to
 * a datasource (sql,ldap,etc)
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class QueryHandler
{

    /**
     * Reference to the datasource object (sql,ldap,csv etc)
     *
     * @var     string
     */
    private $datasource;


    /**
     * Datasource handler type
     *
     * This points to the correct datasource handler
     * such as sql, ldap, etc...
     *
     * @var     string
     */

    private $datasourceHandler;


    /**
     * Array containing all the current query info
     *
     * @var     array
     */
    private $query;


    /**
     * Name of query
     *
     * This name is set in the query file under
     * <query name="..."> and is used to reference
     * The query results in Flow (i.e. //queryname/datavar)
     *
     * @var     string
     */

    private $queryName;



    /**
     * Query loop index
     *
     * This variable determines whether the query
     * will be looped/repeated. This variable is set as
     * <query loop=''> attribute and accepts either
     * an integer (loop count) or a flow value (will repeat
     * for every instance of this value).
     * This allows a query to be repeated multiple times in a row.
     * Query variables can be changed by supplying an array for
     * parameter (<param array='flow/value/that/repeats'/>). The loop
     * will repeat and use the values of these array params sequentially.
     *
     * @var     string
     */

    private $queryLoop = 1;



    /**
     * Reference to the xml definition file
     *
     * @var     string
     */

    private $definition;


    /**
     * Accepts an xml based query and processes it according
     * to passed criteria
     *
     * @param   string      the name of the xml data file
     * @return  boolean     success
     */

    public function __construct($src)
    {
        $this->definition = $src;
        return true;
    }


    /**
     * Accepts an xml based query and processes it according
     * to passed criteria
     *
     * @return  boolean     success
     */

    public function process()
    {

        $this->parseDefinition();

        //get the datasource type (sql,ldap)
        $this->getDatasource($this->query['connection'], $params);
        
        //instantiate the datasource handler
        $this->loadModule($params);
        
        //get the datasource type (sql,ldap)
        $this->getDatasource($this->query['connection'], $params);

        $this->datasource->setConnection();
        

        $this->datasource->execQuery($this->query, $this->queryName, $this->queryLoop);
        
        
        
        unset($this->datasource);

        return true;
    }


    /**
     * Loads datasource handler
     *
     * Loads correct module based on
     * $this->datasourceHandler value and
     * instantiates it.
     *
     * @param   array       module parameters
     */

    private function loadModule(&$params)
    {

        //load the datasource module file based on type
        $datasource_file = NX_PATH_CORE."datasources".DIRECTORY_SEPARATOR . $this->datasourceHandler . "datasource.php";
        if(is_file($datasource_file)) { 
            require_once($datasource_file);
        } else { 
        }
        $class = trim(ucfirst($this->datasourceHandler)) . "Datasource";
        $this->datasource =& new $class($params);
        return true;


    }


    /**
     * Processes and parsers the xml definition file
     * and retrieves query info
     *
     */

    private function parseDefinition()
    {

        /*
        $xml_string = 
'<?xml version="1.0" encoding="UTF-8"?>
';*/
        $my_entities_file = Path::get("//query_db_entities","flow");
        if(!empty($my_entities_file)) { 
            $xml_string .= file_get_contents(NX_PATH_COMPILE.$my_entities_file);
            // This is cheesy I know - see config.php line 237
            $xml_string = str_replace("<deletethisplaceholderinqueryhandler/>","",$xml_string);
        } else { 
            $xml_string = "";
        }
        
        $xml_string .= file_get_contents($this->definition);
        
        //$xml = simplexml_load_file($this->definition);
        $xml = simplexml_load_string($xml_string);
        $this->queryName = (string)$xml['name'];
        $defaultval = (string)$xml['default'];
        $loopvar = (string)$xml['loop'];
        if(!empty($loopvar))
        {

            if(is_numeric($loopvar))
            {
                $this->queryLoop = $loopvar;
            }
            else
            {
               
                $array = Path::get($loopvar,"flow");
               
                /*
                //loop for each array val
                if(is_array($array))
                     $this->queryLoop = sizeof($array);
                //oops. no value. no loopie
                elseif(empty($array) && $array !== '0')
                    $this->queryLoop = 0;
                //single value. loop once and make into array
                else
                    $array = array($array);
                */

                if(is_array($array)) 
                { 
                     $this->queryLoop = sizeof($array);
                } 
                elseif(empty($array) && $array !== '0' && empty($defaultval))
                {
                    // No values at all
                    $this->queryLoop = 0;
                }
                elseif(empty($array) && $array !== '0' && $defaultval==='true')
                {
                    // Use default value
                    $array = array($defaultval);
                }
                else
                { 
                    //single value. loop once and make into array
                    $array = array($array);
                }
            }

        }

        //get paging info (retrieve a set of rows)
        $this->query['rows'] = array();
        
        $rows_limit = (string)$xml->rows[0]['limit'];
        $rows_first = (string)$xml->rows[0]['first'];

        //see if we are given a number or a flow var for 'first' and 'limit'
        if(!empty($rows_first))
        {
            if(is_numeric($rows_first))
                $this->query['rows']['first'] = $rows_first;
            else
            {
                $val = Flow::getByPath($rows_first);
                if(!is_null($val) or !is_array($val))                
                    $this->query['rows']['first'] = $val;
            }
        }
        if(!empty($rows_limit))
        {
            if(is_numeric($rows_limit))
                $this->query['rows']['limit'] = $rows_limit;
            else
            {
                $val = Flow::getByPath($rows_limit);
                if(!is_null($val) or !is_array($val))
                {
                    $this->query['rows']['limit'] = $val;
                }
            }
        }

        //get array of query info (query itself, args, etc)
        
        if(!$this->query['sql'] = (string)$xml->sql)
        {
           Error::init('No query specified in '.$this->definition, NX_ERROR_FATAL);
        }
        if(!$this->query['connection'] = (string)$xml->connection)
        {
            Error::init('No database connection specified!', NX_ERROR_FATAL);
        }

        //make a nice array from in and out values
        
        $key = 0;
        if(isset($xml->params->param))
        {
            foreach($xml->params->param as $val)
            {
				
                $name = (string)$val['name'];
                $this->query['params'][$key]['name'] = !empty($name) ?  $name : false;
                    
                $array = (string)$val['array'];            
                $this->query['params'][$key]['array'] = !empty($array) ?  $array : false;
                                            
                $default = (string)$val['default'];           
                $this->query['params'][$key]['default'] = (!empty($default) OR $default === '0')?  $default : 'NULL';           
                        
                $type = (string)$val['type'];
                $this->query['params'][$key]['type'] = !empty($type) ?  $type : false;
                $key++;
            }
        }
    
        return true;

    }

    /**
     * Retrieves database info from global.xml file based
     * on query type
     *
     * @param   string      datasource name - must match one on global.xml
     * @param   array       database parameters (username, host, password, etc)
     */

    private function getDatasource($name, &$datasource)
    {

        $datasource = Config::getSection('datasource',$name);

        //Debug::dump($datasource);

        //Developer note: This is where you set what Datasource handler you would
        //like to use based on type (<type> in global.xml)
        switch($datasource['type'])
        {
            case 'mysql':
            case 'ibase':
            case 'ifx':
            case 'mssql':
            case 'msql':
            case 'mysql':
            case 'odbc':
            case 'oci':
            case 'pgsql':
                $this->datasourceHandler = 'mdb2sql'; //metabase
                break;
            case 'creolemysql':
                $this->datasourceHandler = 'creolesql'; //creole
                break;
            case 'pdomysql':
                $this->datasourceHandler = 'pdosql'; //pdo
                break;
            case 'pdosqlite':
                $this->datasourceHandler = 'pdosql'; //pdo
                break;
            default:
                Error::init($type.' datasource type is not supported', NX_ERROR_WARNING);
                return false;
                break;
        }

        return true;
    }

} //end class
?>