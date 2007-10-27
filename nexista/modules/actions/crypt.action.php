<?php
/*
 * -File        Action.php - Wed Aug 14 14:34:06 MST 2002
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage <>
 * -Author		Albert Lash <>
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage <>
 * @author      Albert Lash <>
 */
 
/**
 * This action applies crypt hash to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class CryptAction extends Action
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
			$en = $res->item(0)->nodeValue;
			$salt = substr($en, 0, 2);
			$crypt = crypt($en, $salt);
			//$crypt = crypt($res->item(0)->nodeValue,'ug');
			Flow::add("crypt", $crypt);
            return true;
        }
         
        return false;
            

    }
} //end class

?>
