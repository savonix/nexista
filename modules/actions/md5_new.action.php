<?php
/*
 * -File        Action.php - Wed Aug 14 14:34:06 MST 2002
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage <>
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage <>
 */
 
/**
 * This action applies MD5 hash to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Md5_newAction extends Action
{


    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var' => '' //required - name of flow var to md5
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
            $md5 = md5($res->item(0)->nodeValue);
			Flow::add("md5", $md5);
            return true;
        }
         
        return false;
            

    }
} //end class

?>
