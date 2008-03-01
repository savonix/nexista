<?php
/*
 * -File        log.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author      Albert Lash
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
 */
 
/**
 *
 * This action uses PEAR Log to enable logging.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_logAction extends Nexista_Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'code' => '',   //required - 3 letter action code
        'target' => ''  //optional - target ID/name of action
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        // Use PEAR Log
        return true;
    }



} //end class

?>