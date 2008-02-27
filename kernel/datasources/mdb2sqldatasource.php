<?php
/*
 * -File        mdb2sqldatasource.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2007, Nexista
 * -Author 	    albert lash
 *
 */


/**
 * @package     Nexista
 * @subpackage  Datasources
 * @author      Albert Lash
 */
 
/**
 * This class provides functionality to access
 * sql databases through the MDB2 abstraction
 * layer.
 *
 * @tutorial    query.pkg
 * @package     Nexista
 * @subpackage  Datasources
 */

class Nexista_mdb2SqlDatasource
{

    /**
     * Class parameters
     *
     * @var     array
     */

    private $params;
    
    /**
     * Class data
     *
     * @var     array
     */

    private $data;


    /**
     * Query info
     *
     * @var     array
     */

    private $query;


    /**
     * Database handle
     *
     * @var     int
     */

    static public $db;


    /**
     * Query result reference
     *
     * @var     object
     */

    private $result;
    
    /**
     * Assoc array
     *
     * @var     object
     */

    private $result_set;


    /**
     * Temporary array for row result
     *
     * @var     array
     */

    private $rowResult;


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
     * Type of query
     *
     * The type of query (select, insert, etc) This is a
     * marker used to speed process internally
     *
     * @var     string
     */

    private $queryType;


    /**
     * Array of active lob references
     *
     *
     * @var     array
     */

    private $lob;

    /**
     * Constructor
     *
     * @param   array       connection parameters
     * @return  boolean     success
     */

    public function Nexista_Mdb2SqlDatasource(&$params)
    {
            $this->params =& $params;
    }


    /**
     * Establishes datasource connections settings
     *
     * @return  boolean     success
     */

    public function setConnection()
    {
		
        

		$dsn = array(
            "hostspec"=>$this->params['hostname'],
            "phptype"=>"mysqli",
            "username"=>$this->params['username'],
            "password"=>$this->params['password'],
            "database"=>$this->params['database']);


        require_once("MDB2.php");
		$this->db =& MDB2::factory($dsn);
        
        $this->db->setOption('persistent', true);
        $this->db->opened_persistent = true;
        $this->db->connection = $link;
        
        // connect
        
        
		if (PEAR::isError($this->db)) {
            $error = $this->db->getMessage();
            Nexista_Error::init("$error ; Translation = Error connecting to database, check your 
                configuration file, specifically the datasource sections.",NX_ERROR_FATAL);
		}

        return true;
    }


    /**
     * Parses a prepared query for special var types, action codes (_AUTO_, _DATE_, etc)
     * and for general query type (select,insert,etc...) and
     *
     * This method calls metabase prepareQuery(), sets the variable types
     * (see metabase docs) and parses a query to determine its type (select,insert.etc...)
     * and updates $this->queryType with this info. It also looks for
     * special keyword and replaces them with values accordingly.
     * _AUTO_ : retrieves sequnce info (auto-increment) for the table,
     * increments it and returns the new value.
     * _DATE_ : inserts current timestamp
     * This functiomn returns a prepared query handler resource by reference
     *
     * @param   integer     returning prepared query handler
     * @param   integer     current loop count
     * @return  boolean     success
     */

    private function prepareQuery(&$sql, $loop)
    {

        //see if it is a select
        if (eregi("^[[:space:]]*select", $this->query['sql']))
        {
            $this->queryType = 'select';

        } elseif (eregi("^[[:space:]]*show", $this->query['sql'])) {
            $this->queryType = 'select';
        }

        $count = 1;

        if (isset($this->query['params']))
        {       
            foreach($this->query['params'] as $val)
            {
                $found = true;
                $path = new Nexista_Path();
                if(!empty($val['name']))
                {           
                    $value = $path->get($val['name'], 'flow');
                    if(is_null($value) && ($val['type'] == 'integer'))
                    {
                         $found = false;
                    }
                }
                elseif(!empty($val['array']))
                {
                    $array = $path->get($val['array'], 'flow');
                    if(!is_array($array))
                        $array = array($array);
                    $value = $array[$loop];
                } else { 
                    $found = false;
                }
                
                if(((!$found) || ($value === 'NaN') || ($value === '') || ($value == '')) && $value!=='0')
                {
                    $value = $val['default'];
                }

                if($value === 'NULL')
                { //$type = NULL;
                }
                else
                {
                    $type = $val['type'];
                }

                if($value || $value==0)
                {
					$types[] = $type;
					$data[] = $value;
                }
                $count++;
            }

            if($this->queryType=="select") { 
                $this->data = $data;
                $database_cache = Nexista_Config::get('./runtime/database_cache');
                if(function_exists(xcache_get) && $database_cache=="1") {
                    $cache_name = $this->getQueryId();
                    if(xcache_isset($cache_name)) 
                    { 
                        $result_set = unserialize(xcache_get($cache_name));
                        return $result_set;
                    } 
                }
            }

            $this->db->connect();
			$prep = $this->db->prepare($this->query['sql'], $types);
            if (PEAR::isError($prep)) {
                Nexista_Error::init($result->getMessage().$this->queryName,NX_ERROR_FATAL);
            }
            $result = $prep->execute($data);

        } else { 
            $prep = $this->db->prepare($this->query['sql'], $types);  
            $result = $prep->execute(); 
        }

        if (PEAR::isError($result)) {
            $my_debug_result = serialize($result);
            Nexista_Error::init($result->getMessage().$this->queryName.$my_debug_result,NX_ERROR_FATAL);
        }
        $prep->free();

        if($this->queryType=="select") { 
            //$this->result = $result;
            return $result->fetchAll(MDB2_FETCHMODE_ASSOC);
        } else {
            return true;
        }
    }


    /**
     * Shutdown and resource clear
     *
     */

    public function __destruct()
    {
    

    }



    /**
     * Executes a query
     *
     * @param   mixed       query
     * @param   string      name of query
     * @param   integer     loop count
     * @return  boolean     success
     */

    public function execQuery($query, $queryName, $queryloop)
    {

         // What is this?
        $this->query =& $query;
        $this->queryName =& $queryName;
		

        for($loop = 0; $loop < $queryloop; $loop ++)
        {
            if(!$this->result_set=$this->prepareQuery($sql, $loop))
            {
                // Error
            }
            if ($this->queryType == 'select')
            {
                  $this->storeResult();
            }
        }

        return true;


    }


    /**
     * Parses headers with query result
     *
     * This is a callback function used to parse lob headers with db data.
     *
     * @param   array       match result from parsing lob headers
     * @return  string      array element
     */

    private function resultParserCallback($match)
    {
        return $this->rowResult[$match[1]];
    }


    /**
     * Assigns query result to flow
     *
     * @return  boolean success
     */

    public function storeResult()
    {
    
		$debug = false;
        
        if($this->result_set)
        {
            $result_set = $this->result_set;
            $caching = Nexista_Config::get('runtime/cache');
            if(strlen(serialize($result_set))>100 && $caching==1 && isset($cache_is_of_for_now)) {
            if(function_exists(xcache_set)) {
                $cache_name = $this->getQueryId();
                xcache_set($cache_name, serialize($result_set), 10000000);
            }
            }
            $cols = array_flip(array_keys($result_set[0]));
            //print_r($cols);
			$row = 0;
			$number_of_rows=count($result_set);
            while($row < $number_of_rows)
            {
                $flow = Nexista_Flow::singleton();
                $q = $flow->root->appendChild($flow->flowDocument->createElement($this->queryName));

                foreach($cols as $key => $val)
                {
                    $myval = $result_set[$row][$key];
                    if($debug===true) {
                    echo "Stuff: $key, $myval<br/>";
                    }
                    $myval = htmlspecialchars($myval);
                    $q->appendChild($flow->flowDocument->createElement($key,$myval));
                }
				$row++;
                $xml_string .=  $flow->flowDocument->saveXml($q);
            }
            $cache_name = $this->getQueryId($data);
            return true;
        }
        return false;
    }




    /**
     * Retrieves db field/column name from a query
     *
     * @param   integer     count of desired column in query
     * @return  string      name of column in db
     */

     private function getFieldName($count)
     {
        //TODO this needs to be extensively tested with different query phrasings
        $field = false;
        //see if INSERT
        if(stristr($this->query['sql'], 'INSERT'))
        {
             
            //get query row names and values
            preg_match("~INSERT.*\(\s*(\w.*)\s*\).*VALUES\s*\(\s*(\w.*)\s*\)~m", $this->query['sql'], $fields);

            $qry_val = preg_split ('~[\s]*,[\s]*~', $fields[2]);
            $qry_name = preg_split ('~[\s]*,[\s]*~', $fields[1]);

            for($i = 0; $i < sizeof($qry_name); $i++)
            {
                if($qry_val[$i] != '?')
                {
                    //offset count for this value
                    $count ++;
                }
                elseif($i == $count)
                {
                    $field = trim($qry_name[$i]);
                    break;
                }
            }
        }
        //assume UPDATE
        else
        {

            preg_match("~SET\s*(\w.*(.[^,]))\s+([^\W,])~Um", $this->query['sql'], $fields);
            $fields = preg_split ('~[\s]*,[\s]*~', $fields[1]);

            for($i = 0; $i < sizeof($fields); $i++)
            {
                if(!stristr($fields[$i], '?'))
                {
                    //offset count for this value
                    $count ++;
                }
                elseif($i == $count)
                {

                    $field = split('=', $fields[$count]);
                    $field = trim($field[0]);
                    break;
                }
            }
        }

        return $field;

     }


    /**
     * Creates a unique query name for naming cache files
     *
     * @return      integer     crc32 of prepared query array
     */

    public function getQueryID()
    {

        return $this->queryName.md5(serialize($this->data));
        //return $this->queryName;
    }
}


?>