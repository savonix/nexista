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
            'host'          => $this->params['hostname']
        );

        // Connecting using the configuration:
        $this->ldap = Net_LDAP::connect($config);

        // Testing for connection error
        if (PEAR::isError($ldap)) {
            die('Could not connect to LDAP-server: '.$ldap->getMessage());
        }
    }


    public function execQuery($search,$queryName,$filter,$options='')
    {

        $this->queryName =& $queryName;
        // Test for search errors:
        if (PEAR::isError($this->ldap)) {
            die($this->ldap->getMessage() . "\n");
        }
        // Perform the search!
        $options = array(
            'scope' => 'one',
            'attributes' => array('*')
        );
        $search = $this->ldap->search($search['searchbase'],NULL,$options);
        $this->result_set = $search->entries();
        if (PEAR::isError($search)) {
            die($search->getMessage() . "\n");
        }
        $this->storeResult();

    }

    /**
     * Assigns query result to flow
     *
     * @return  boolean success
     */

    public function storeResult()
    {

        foreach ($this->result_set as $dn => $entry) {
            $flow = Nexista_Flow::singleton();
            $q = $flow->root->appendChild($flow->flowDocument->createElement($this->queryName));
            $cn = $entry->getValue('cn', 'single');
            $key = 'ipHostNumber';
            $ip = $entry->getValue($key, 'single');
            $myval = htmlspecialchars($ip);
            $q->appendChild($flow->flowDocument->createElement($key,$myval));
            //echo "$ip    $cn\n";
        }
        return false;
    }
} // end class

?>