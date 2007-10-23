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

class ConcatAction extends Action
{


    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var1' => '', //required
        'var2' => '' //required
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$res1 = Flow::find($this->params['var1']);
		$res2 = Flow::find($this->params['var2']);
        if($res1->length === 1)
        {            
            $res1->item(0)->nodeValue = $res1->item(0)->nodeValue.$res2->item(0)->nodeValue;
            return true;
        }
         
        return false;
            

    }
} //end class

?>
