<?php
/*
 * -File        concat.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Albert Lash
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * This action concatenates params
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_ConcatAction extends Nexista_Action
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

		$res1 = Nexista_Flow::find($this->params['var1']);
		$res2 = Nexista_Flow::find($this->params['var2']);
        if($res1->length === 1)
        {
            $res1->item(0)->nodeValue = $res1->item(0)->nodeValue.$res2->item(0)->nodeValue;
            return true;
        }

        return false;

    }
} //end class

?>