<?php
/*
 * -File        metabasesqldatasource.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002-2007, Nexista
 * -Author      joshua savage 
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
 * @package     Nexista
 * @subpackage  Datasources
 */

class MetabaseSqlDatasource
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

    public function MetabaseSqlDatasource(&$params)
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
        // Throw error if these can't be read.
        if(!is_readable(NX_PATH_LIB . "../../metabase/metabase_interface.php")) { 
                echo "Can't find or access metabase... quitting";
                exit;
        }
        require_once(NX_PATH_LIB . "../../metabase/metabase_interface.php");
        require_once(NX_PATH_LIB . "../../metabase/metabase_database.php");
        require_once(NX_PATH_LIB . "../../metabase/metabase_lob.php");

        $error = MetabaseSetupDatabaseObject(array(
            "Host"=>$this->params['hostname'],
            "Type"=>$this->params['type'],
            "User"=>$this->params['username'],
            "Password"=>$this->params['password'],
			"port"=>$this->params['port'],
            "IncludePath"=> NX_PATH_LIB . '../../metabase/'),$this->db);

        if($error!="")
        {
            Error::init("Database setup error: $error", NX_ERROR_FATAL);
        }

        $this->db->SetDatabase($this->params['database']);
        $this->db->CaptureDebugOutput(true);


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
        } elseif  (eregi("^[[:space:]]*show", $this->query['sql'])) {
            $this->queryType = 'select';
        }
        else
        {
            //parse query for any sequence and set it
            $this->setKeywords();
        }

        $sql = $this->db->prepareQuery($this->query['sql']);

        if (!$sql)
        {
            Error::init($this->db->error(), NX_ERROR_FATAL);
        }

        $count = 1;
        $type_functions = array(
                                'text' => "QuerySetText",
                                'boolean' => "QuerySetBoolean",
                                'integer' => "QuerySetInteger",
                                'decimal' => "QuerySetDecimal",
                                'float' => "QuerySetFloat",
                                'date' => "QuerySetDate",
                                'time' => "QuerySetTime",
                                'timestamp' => "QuerySetTimestamp",
                                'blob' => "QuerySetBlob",
                                'clob' => "QuerySetClob",
                                'null' => "QuerySetNull"
                             );

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

                   
                //conditional query?
                if(!empty($val['condition']))
                {
               
               
                    $res = Path::get($val['condition'], 'flow');                   
                    //evaluate to null/false - remove whole section
                    if(is_null($res) or !$res)
                    {
                        $value = 'WE';
                    }
                }
                    
                //not found. default/ Note that if no default is provided in query def, default = NULL - (see queryhandler)
                //if((!$found) || ($value === 'NaN') || ($value === ''))
                if(((!$found) || ($value === 'NaN') || ($value === '') || ($value == '')) && $value!=='0')
                {
                    $value = $val['default'];

                }


                // Get the argument type. ie. 'text'.

                //set the null value if any
                if($value === 'NULL')
                {
                    $type = 'null';
                }
                else
                {
                    $type = $val['type'];
                }

                /*
                //handle xsl NaN values and set them to NULL
                if($value == 'NaN')
                {   die("SD");
                    //TODO. can we do better than NULL? is there any way to get the DB defaults in a prepared query?
                    $value = 'NULL';
                }*/



                if($type)
                {
                    if(!isset($type_functions[strtolower($type)]))
                    {
                        Error::init("Metabase type: $type does not exist", NX_ERROR_FATAL);
                    }

                    $function = $type_functions[$type];

                    //see if lob
                    if($type == 'blob' or $type == 'clob')
                    {

                        //set up a handler to get the blob. right now we just have file, upload (same as file but moves file first)
                        //not sure this is useful. kinda prevents things like making thumbs
                        //note - I commented out the section in queryhandler that looks for this lob attrib
                        /*
                        if($val['lob'] == 'upload')
                        {

                            $tmpname = NX_PATH_TMP.'upload_'.rand(1,1000);
                            if(!move_uploaded_file($value, $tmpname))
                            {
                                Error::init('Unable to upload file for lob query: '.$value. ' to '.$tmpname, NX_ERROR_FATAL);
                            }
                            chmod($tmpname,0644);

                            $lobhandler = array(
                            'Database' => $this->db->database,
                            'Error' => '',
                            'FileName' => $tmpname,
                            'Type' => 'inputfile'
                            );

                        }
                        elseif($val['lob'] == 'file')
                        {
                            $lobhandler = array(
                            'Database' => $this->db->database,
                            'Error' => '',
                            'FileName' => $value,
                            'Type' => 'inputfile'
                            );
                        }
                        else
                        {
                            Error::init('No lob handler type specified for query', NX_ERROR_FATAL);
                        }
                        */
                        //default to file for now - this replaces commented out section above until further notice
                        //if we decide to have multiple handlers
                        $lobhandler = array(
                            'Database' => $this->db->database,
                            'Error' => '',
                            'FileName' => $value,
                            'Type' => 'inputfile'
                            );


                        //get row name
                        $field = $this->getfieldName($count);

                        //TODO catch whatever the $lobhandler['Error'] field has - not implemented whatever it is
                        if(MetabaseCreateLOB($lobhandler,$lob))
                        {
                            //keep track of lob to destroy later
                            $this->lob[] = $lob;


                            if (!$this->db->$function($sql, $count, $lob, $field))
                            {
                                Error::init($this->db->error(), NX_ERROR_FATAL);
                            }

                        }
                        else
                        {
                            Error::init($this->db->error(), NX_ERROR_FATAL);
                        }


                    }

                    //not a lob
                    elseif (!$this->db->$function($sql, $count, $value))
                    {                     
                        Error::init($this->db->error().' - current query param: '.$val['name'], NX_ERROR_FATAL);
                    }
                }

                else
                {
                    $this->db->QuerySet($sql, $count, "raw", $value);
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
    

       // $this->db->CloseSetup();
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

        //see if we have a request for a rowset
        if(isset($this->query['rows']['first']) and isset($this->query['rows']['limit']) )
        {
            if(!$this->db->SetSelectedRowRange((int)$this->query['rows']['first'], (int)$this->query['rows']['limit']))
            {
                Error::init($this->db->error().' - Query: '.$this->queryName, NX_ERROR_FATAL);
            }
        }

        for($loop = 0; $loop < $queryloop; $loop ++)
        {
            if(!$this->prepareQuery($sql, $loop))
            {
                    return false;
            }
             
            //echo $this->query['sql'];
            //Debug::dump($query);
            //echo $this->db->Debug();
            $this->result = $this->db->executeQuery($sql);

            //Debug::message($this->db->DebugOutput());

            if(!$this->result)
            {
                //echo $this->db->debug_output;
                Error::init($this->db->error().' - Query: '.$this->queryName, NX_ERROR_FATAL);
            }


            //is select, store result in Flow
            if ($this->queryType == 'select')
            {
                //see if we have a lob. For now we dump to screen
                if(isset($this->query['lob']))
                {
                    $this->getLob();
                }
                else
                {
                    //TODO return success
                    $this->storeResult();
                }
            }
          
            //free resources for this query
            $this->db->freePreparedQuery($this->query['sql']);

            for($i = 0; $i < count($this->lob); $i++)
                MetabaseDestroyLOB($this->lob[$i]);


        }
        /*
        else
        {
            //set success attrib in queryname to true
            $GLOBALS['__FLOW__']->addTag($this->queryName, array('success'=>'true'), '0', $path);
        }

        */

        return true;


    }


    /**
     * Dumps query result to screen
     *
     * This is used with lobs. It reads the <lob> info from a query and processes it
     * accordingly, dumping headers first if specified
     *
     * todo  the lob results are dumped to screen right here. 
     * This prevents any caching.
     *  We need either:
     * - a method to add to the gate files that will update $ouput with this data
     * - some global ref to $output to access from here (concerned about having $output as a * global
     * @return  boolean     success
     */

    public function getLob()
    {
        if($this->result != 0)
        {
            //get fields
            //TODO some error check - see metabase info about this function
            $this->db->getColumnNames($this->result, $cols);

            //get list of lobs so we don't output those with standard data
            foreach($this->query['lob'] as $k=>$l)
                $lob_fields[] = $l['field'];

            for($row = 0; ($eor = $this->db->endOfResult($this->result)) == 0; $row ++)
            {

                //deal with everything but the lobs - must do first to parse header and whatever else
                foreach($cols as $key => $val)
                {
                    //currently we ignore null values in flow (no variable = null, empty variable = blank string)
                    if((!$this->db->resultIsNull($this->result, $row, $key)) and (!in_array($key, $lob_fields)))
                    {
                        $this->rowResult[$key] = $this->db->FetchResult($this->result, $row, $key);
                    }
                }

                //TODO make a note somewhere about CLOB/BLOB - right now all supported db appear
                //to use the same internal query so it doesnt seem to matter if CLOB or BLOB is used.

                //now we do lobs
                foreach($this->query['lob'] as $key=>$lob)
                {

                    $biggy = $this->db->FetchCLOBResult($this->result, $row,$lob['field']);
                    if($biggy)
                    {
                        //see if we have a header to parse and dump
                        foreach($lob['header'] as $k=>$h)
                        {
                            $header = preg_replace_callback('~\{(.*)\}~Um', array($this,'resultParserCallback'), $h);
                            header($header);
                        }
                            while(!MetabaseEndOfLOB($biggy))
                        {
                            if(MetabaseReadLOB($biggy, $data, $lob['buffer']) < 0)
                            {
                                Error::init(MetabaseLOBError($biggy), NX_ERROR_FATAL);
                                break;
                            }
                            echo $data;
                        }
                        MetabaseDestroyLOB($biggy);

                    }
                }
            }
            $this->db->FreeResult($this->result);
            return true;
        }
        $this->db->FreeResult($this->result);
        return false;
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
    
        //TODO is it possible to patch metabase so that it intefaces directly with flow? check.
        if($this->result != 0)
        {
            //TODO some error check - see metabase info about this function
            $this->db->getColumnNames($this->result, $cols);


            for($row = 0; ($eor = $this->db->endOfResult($this->result)) == 0; $row ++)
            {

                $flow = Flow::singleton();
                $q = $flow->root->appendChild($flow->flowDocument->createElement($this->queryName));
                //$q =& $flow->add($this->queryName);

                foreach($cols as $key => $val)
                {

                    //currently we ignore null values in flow (no variable = null, empty variable = blank string)
                    if(!$this->db->resultIsNull($this->result, $row, $key))
                    {	
						$myval = $this->db->FetchResult($this->result, $row, $key);
						//$myval = iconv("UTF-8","UTF-8//IGNORE",$myval);
						$myval = htmlspecialchars($myval);
                        $q->appendChild($flow->flowDocument->createElement($key,$myval));
                        //$r =& $q->addChild($key);

                        //$r->addValue($this->db->FetchResult($this->result, $row, $key));


                    }
                }

            }
            //cache results //TODO check global caching flag (is there one? :)
            //$filename = NX_PATH_CACHE.'sql-'.$this->getQueryID().'.xml';
            //$GLOBALS['__FLOW__']->cacheTag($filename, $path); //change this to new flow system if we need this

            $this->db->FreeResult($this->result);
            return true;

        }
        $this->db->FreeResult($this->result);
        return false;


    }


    /**
     * Parses insert queries for any sequence (_AUTO_)
     * and replaces them with appropriate values
     *
     */

    public function setKeywords()
    {
        //TODO this needs to be extensively tested with different query phrasings
        if(preg_match("~INSERT.*\(\s*(\w.*)\s*\).*VALUES\s*\(\s*(\w.*)\s*\)~s", $this->query['sql'], $matches))
        {
            //Debug::dump($matches);
            $qry_val = preg_split ('~[\s]*,[\s]*~', $matches[2]);

            //_AUTO_ (auto increment sequence)
            $seq = array_search('_AUTO_', $qry_val);

            if($seq !== false)
            {
            
                $qry_name = preg_split ('~[\s]*,[\s]*~', $matches[1]);
                $seq_name = $qry_name[$seq];

                if(!$this->db->getSequenceNextValue($seq_name, $value))
                {
                    Error::init($this->db->error(), NX_ERROR_WARNING);
                    return false;
                }

                //assign sequence to $GLOBALS (flow:_globals/metabase_sequences/sequence_name) for later access
                $GLOBALS['metabase_sequences'][$seq_name] = $value;

                //replace _AUTO_ with new sequence value
                $this->query['sql'] = str_replace('_AUTO_', $this->db->GetIntegerFieldValue($value), $this->query['sql']);
                
            }
        }

        return true;
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