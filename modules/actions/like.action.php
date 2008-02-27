<?php
/*
 * -File        like.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * This action adds %'s to data for using a LIKE SQL query
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_LikeAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var' => '' //required - name of flow var to edit
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$res = Nexista_Flow::find($this->params['var']);
        if($res->length === 1)
        {            
            $res->item(0)->nodeValue = "%" . $res->item(0)->nodeValue . "%";
            return true;
        }
         
        return false;

    }
} //end class

?>