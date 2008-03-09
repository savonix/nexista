<?php
/*
 * -File        datetime.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004, Savonix Corporation
 * -Author      Albert Lash
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
 */
 
/**
 * This action is a multi-function date and time converter.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_DatetimeAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array   
     * @access  private
     */
    
    var $params = array(
		'source_datetime' => '', 
        'source_format' => '', 
		'target_format' => '', 
		'timezone_offset' => '' =
        );
            
    
    /**
     * Applies filter
     *
     * @return  boolean success
     * @access  private
     */
    
    function main()
    {



    }
} //end class

?>