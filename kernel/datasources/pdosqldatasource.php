<?php
/*
 * -File        pdosqldatasource.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2006, Nexista
 * -Author      joshua savage, 
 * -Author 	    albert lash
 */

/**
 * @package     Nexista
 * @subpackage  Datasources
 * @author      Joshua Savage 
 */
 
/**
 * This class provides functionality to access
 * sql databases through the Metabase abstraction
 * layer.
 *
 * @tutorial    query.pkg
 * @package     Nexista
 * @subpackage  Datasources
 */

class PdoSqlDatasource
{

    /**
     * Class parameters
     *
     * @var     array
     */

    private $params;


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
     * SQL handle
     *
     * @var     int
     */

    static public $sth;


    /**
     * Query result reference
     *
     * @var     object
     */

    private $result;


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

    public function SqlDatasource(&$params)
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
		
//$this->dbh = new PDO('mysql:host=127.0.0.1;dbname=database', 'username', 'password', array(PDO::ATTR_PERSISTENT => true));
try {
    $this->db = new PDO('sqlite:/usr/share/phrequalite/cache/mydb.db');

} catch (PDOException $e) { 
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
//$this->dbh = new PDO('sqlite::memory:');
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

    private function prepareStatement(&$sql, $loop)
    {
		$mysql = $this->query['sql'];
        if (eregi("^[[:space:]]*select", $this->query['sql']))
        {
            $this->queryType = 'select';
        } elseif  (eregi("^[[:space:]]*show", $this->query['sql'])) {
            $this->queryType = 'select';
        }
        
        try{
            $this->sth = $this->db->prepare($mysql);
            $this->sth->execute();

        } catch (PDOException $e) { 
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        $count = 0;
        if (isset($this->query['params']))
        {       
			
            foreach($this->query['params'] as $val)
            {
                $found = true;
                $path = new Path();
                if(!empty($val['name']))
                {           
                  
                    $value = $path->get($val['name'], 'flow');
                  
                    if(is_null($value)  && ($val['type'] == 'integer'))
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
                }
                else
                    $found = false;
					
                if(((!$found) || ($value === 'NaN') || ($value === '') || ($value == '')) && $value!=='0')
                {
                    $value = $val['default'];

                }

                 
				try { 
					$this->myvalue[$count]=$value;
				} catch (SQLException $sqle) {
					print "There was an error executing $sql\n";
					print $sqle;  // will implicitly invoke __toString()
				}
                $count++;   
            }
        }
        return true;
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
         
        $this->query =& $query;
        $this->queryName =& $queryName;



        for($loop = 0; $loop < $queryloop; $loop ++)
        {
            if($this->prepareStatement($sql, $loop)===false)
            {
                    echo "EROR";
                    return false;
            }
             
            $sql = $this->query['sql'];
            try {
                $this->sth->execute();
            }  catch (PDOException $e) { 
                print "Error!: " . $e->getMessage() . "<br/>";
                die();
            }

            //is select, store result in Flow
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
		
		try {
          foreach ($this->sth->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $flow = Flow::singleton();
                $q = $flow->root->appendChild($flow->flowDocument->createElement($this->queryName));
                foreach($row as $key => $value) {
                    if(!$value=="")
                    {	
                        $myval = $value;
                        $myval = htmlspecialchars($myval);
                        if($myval=="NULL") { 
                            $myval="";
                        }
                        $q->appendChild($flow->flowDocument->createElement($key,$myval));
                        //echo "<pre>KEY $key - VALUE $value </pre><br/>";
                    }
                }
			}
		}
		catch (PDOException $e) {
		  print $e->getMessage();
		}

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

        return md5(serialize($this->db->prepared_queries));

    }
}
?>