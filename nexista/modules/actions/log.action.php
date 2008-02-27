<?php
/*
 * -File        log.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage, 
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * CUSTOM USER ACTION
 * 
 * This action needs to be written so that the log utility can write to 
 * a user definable log facility, like syslog or syslog-ng. 
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
    
        
        
        return true;
    }



} //end class

?>