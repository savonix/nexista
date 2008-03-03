<?php
/*
 * -File        ldapdatasource.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author 	    Albert Lash
 *
 */


/**
 * @package     Nexista
 * @subpackage  Datasources
 * @author      Albert Lash
 */
 
/**
 * This class provides functionality to access
 * ldap directories via PEAR's Net_LDAP package.
 *
 * @package     Nexista
 * @subpackage  Datasources
 */

class Nexista_ldapDatasource
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
     * The type of query (search, add, delete, modify, etc.)
     * Should be set in <query name="..." type="...">
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

    public function Nexista_ldapDatasource(&$params)
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
    
        // Inclusion of the Net_LDAP package:
        require_once 'Net/LDAP.php';
    
        // The configuration array:
        $config = array (
            'binddn'        => $this->params['binddn'],
            'bindpw'        => $this->params['bindpw'],
            'basedn'        => $this->params['basedn'],
            'host'          => $this->params['host']
        );
        
        // Connecting using the configuration:
        $this->ldap = Net_LDAP::connect($config);
        
        // Testing for connection error
        if (PEAR::isError($ldap)) {
            die('Could not connect to LDAP-server: '.$ldap->getMessage());
        }
    }

    public function prepareQuery() 
    {
        // What type of transaction?
        switch($this->queryType)
        {
            case "search":
                break;
            case "add":
                break;
            default:
                break;
        }
        
        // Depending on transaction type, what are the parameters?
        
        // Execute the parameters
        
    }
    
    public function getEntry()
    {
        
        
    }
    
    public function search($searchbase,$filter,$options)
    {
        
        // Perform the search!
        $search = $this->ldap->search($searchbase);
        // Test for search errors:
        if (PEAR::isError($search)) {
            die($search->getMessage() . "\n");
        }
        
    }
    
    public function moveEntry()
    {
        
    }
    
    public function deleteEntry()
    {
        
    }
    
    public function addEntry()
    {
        
    }
    

}


?>