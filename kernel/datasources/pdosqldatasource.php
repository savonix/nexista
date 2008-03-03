<?php
/*
 * -File        pdosqldatasource.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author 	    Albert Lash
 */

/**
 * @package     Nexista
 * @subpackage  Datasources
 * @author      Albert Lash
 */
 
/**
 * This class provides functionality to access
 * sql databases through the Metabase abstraction
 * layer. As of 2008 is is completely unstable.
 *
 * @package     Nexista
 * @subpackage  Datasources
 */

class Nexista_PdoSqlDatasource
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
		
        try {
            $this->db = new PDO('sqlite:/usr/share/phrequalite/cache/mydb.db');
        
        } catch (PDOException $e) { 
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
        return true;
    }


    /**
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



}
?>