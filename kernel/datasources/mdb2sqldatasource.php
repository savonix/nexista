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
            "phptype"=>$this->params['type'],
            "username"=>$this->params['username'],
            "password"=>$this->params['password'],
            "database"=>$this->params['database']);


        require_once("MDB2.php");
		$this->db =& MDB2::factory($dsn);
        
		if (PEAR::isError($this->db)) {
            $error = $this->db->getMessage();
            Nexista_Error::init("$error ; Translation = Error connecting to database, check your 
                configuration file, specifically the datasource sections.",NX_ERROR_FATAL);
		}
        $this->db->setOption('persistent', true);
        $this->db->opened_persistent = true;
        $this->db->connection = $link;
        

        return true;
    }


    /**
     * Parses a prepared query for general query type (select,insert,etc...) and
     *
     * This method calls MDB2 prepareQuery(), sets the variable types
     * (see MDB2 docs) and parses a query to determine its type (select,insert.etc...)
     * and updates $this->queryType with this info. It also looks for
     * special keyword and replaces them with values accordingly.
     * This function returns a prepared query handler resource by reference
     *
     * @param   integer     returning prepared query handler
     * @param   integer     current loop count
     * @return  boolean     success
     */

    private function prepareQuery(&$sql, $loop)
    {

        //see if it is a select
        if(!isset($this->queryType)) {
            if (eregi("^[[:space:]]*select", $this->query['sql']))
            {
                $this->queryType = 'select';
            }
            elseif (eregi("^[[:space:]]*show", $this->query['sql'])) {
                $this->queryType = 'select';
            }
        }

        $count = 1;

        if (isset($this->query['params']))
        {
            foreach($this->query['params'] as $val)
            {
                $found = true;
                $path = new Nexista_Flow();
                if(!empty($val['name']))
                {
                    $value = $path->getByPath($val['name']);
                    if(is_null($value) && ($val['type'] == 'integer'))
                    {
                         $found = false;
                    }
                }
                elseif(!empty($val['array']))
                {
                    $array = $path->getByPath($val['array']);
                    if(!is_array($array))
                        $array = array($array);
                    $value = $array[$loop];
                }
                /*
                // Unfortunately plain XPath 1.0 cannot access the node-name 
                // itself, this will do so for an array, where you'd likely
                // want that information 
                */
                elseif(!empty($val['node-name-array']))
                {
                    $array = $path->getByPath($val['node-name-array'],'ASSOC');
                    if(!is_array($array))
                        $array = array($array);
                    $key = array_keys($array[$loop]);
                    $value = $key[0];
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

            //$this->db->connect();
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
            return $result->fetchAll(MDB2_FETCHMODE_ASSOC);
        } else {
            return true;
        }
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

        $this->query =& $query;
        $this->queryName =& $queryName;
		

        for($loop = 0; $loop < $queryloop; $loop ++)
        {
            if(!$this->result_set=$this->prepareQuery($sql, $loop))
            {
                return false;
            }
            if ($this->queryType == 'select')
            {
                  $this->storeResult();
            }
        }

        return true;

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

            $cols = array_flip(array_keys($result_set[0]));
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
            return true;
        }
        return false;
    }

}


?>