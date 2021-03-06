<?php
/**
 * -File        Query.Handler.php
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
 * This class is the query handler.
 * It uses an xml query and retrieves from or sends data to
 * a datasource (sql,ldap,etc)
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class Nexista_QueryHandler
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
        $datasource_file = NX_PATH_BASE.'modules/datasources/' . $this->datasourceHandler . 'datasource.php';
        if (is_file($datasource_file)) {
            require_once($datasource_file);
        } else {
        }
        $class = 'Nexista_' . trim(ucfirst($this->datasourceHandler)) . 'Datasource';
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
        // Here we use see whether to use the default table names or if 
        // a entity set has been specified.
        $server_name = $_SERVER['SERVER_NAME'];

        $myPrefix =
            Nexista_Config::get('./datasource[@id="'.$server_name.'"]/prefix');

        $mydtd = Nexista_Config::get('./datasource[@id="'.$server_name.'"]/dtd');

        // this can customize the table prefix based on vhosts in config
        if (!empty($myPrefix)) {
            $xmlString =
                "<!DOCTYPE query [".file_get_contents(dirname($this->definition)."/".$mydtd);
			$rment     = '/(\<\!ENTITY prefix "[a-z0-9A-Z]*"\>)/';
            $xmlString = preg_replace($rment, '', $xmlString);
			$xmlString .= '<!ENTITY prefix "'.$myPrefix.'">]>';
            $xmlString .= file_get_contents($this->definition);
            $rmme      = '/(\<\!DOCTYPE query SYSTEM "'.$mydtd.'"\>)/';
            $xmlString = preg_replace($rmme, '', $xmlString);
            $xml       = simplexml_load_string($xmlString, null,
                LIBXML_COMPACT | LIBXML_DTDLOAD);
        } else {
            $xml = simplexml_load_file($this->definition, null,
                LIBXML_COMPACT | LIBXML_DTDLOAD);
        }
        $this->queryName = (string)$xml['name'];
        if (!empty($xml['name'])) {
            $this->queryType = (string)$xml['type'];
        }
        $defaultval = (string)$xml['default'];
        $loopvar    = (string)$xml['loop'];
        if (!empty($loopvar)) {
            if (is_numeric($loopvar)) {
                $this->queryLoop = $loopvar;
            } else {
                $array = Nexista_Path::get($loopvar,'flow');
                if (is_array($array)) {
                     $this->queryLoop = sizeof($array);
                } elseif (empty($array) && $array !== '0' &&
                    empty($defaultval)) {
                    // No values at all
                    $this->queryLoop = 0;
                } elseif (empty($array) && $array !== '0' &&
                    $defaultval==='true') {
                    // Use default value
                    $array = array($defaultval);
                } else {
                    //single value. loop once and make into array
                    $array = array($array);
                }
            }
        }


        //get array of query info (query itself, args, etc)
        if (!$this->query['sql'] = (string)$xml->sql) {
            // no sql node, maybe an ldap search?
            if (!$this->query['searchbase'] = (string)$xml->searchbase) {
                Nexista_Error::init('No query specified in '.$this->definition,
                    NX_ERROR_FATAL);
            } else {
                $this->query['filter']  = (string)$xml->filter;
                $this->query['options'] = (string)$xml->options;
            }
        }
        if (!$this->query['connection'] = (string)$xml->connection) {
            Nexista_Error::init('No database connection specified!', NX_ERROR_FATAL);
        }

        //make a nice array from in and out values
        $key = 0;
        if (isset($xml->params->param)) {
            foreach ($xml->params->param as $val) {

                $name = (string)$val['name'];
                $this->query['params'][$key]['name'] = !empty($name) ? $name : false;

                $array = (string)$val['array'];
                $this->query['params'][$key]['array'] = !empty($array) ? $array : false;

                $array = (string)$val['node-name-array'];
                $this->query['params'][$key]['node-name-array'] = !empty($array) ? $array : false;

                $default = (string)$val['default'];
                $this->query['params'][$key]['default'] =
                    (!empty($default) OR $default === '0')?  $default : false;

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

        $datasource = Nexista_Config::getSection('datasource', $name);

        //Caution: this will output the password as well.
        //Nexista_Debug::dump($datasource);

        //Developer note: This is where you set what Datasource handler you would
        //like to use based on type (<type> in global.xml)
        switch($datasource['type'])
        {
            case 'mysql':
            case 'mysqli':
            case 'sqlite':
            case 'pgsql':
                $this->datasourceHandler = 'mdb2sql'; //metabase
                break;

            case 'ldap':
                $this->datasourceHandler = 'ldap'; //ldap
                break;

            default:
                Nexista_Error::init($type.' datasource type is not supported',
                    NX_ERROR_WARNING);
                return false;
                break;
        }

        return true;
    }

} //end class
?>
