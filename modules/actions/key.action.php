<?php
/*
 * -File        Action.php - Fir Jul 8 08:00:00 EST 2005
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2005, Savonix
 * -Author      Albert Lash <>
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash <>
 */
 
/**
 * Temporarily puts unencrypted key into flow for re-encryption.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class KeyAction extends Action
{


    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$res = Flow::find($this->params['var']);
        if($res->length === 1)
        {          
			$key=$_SESSION['NX_AUTH']['key'];
			Flow::add("key", $key);
            return true;
        }
         
        return false;
            

    }
} //end class

?>
