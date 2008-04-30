<?php
/*
 * -File        add1.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002-2007, Nexista
 * -Author      joshua savage
 * -Author      albert lash
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * This action applies adds one to the parameter, used with database sequences, 
 * can probably be deprecated.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_Add1Action_ extends Nexista_Action
{

    // NOTE: This action is deprecated.

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
        
		$res = Nexista_Flow::find($this->params['var']);
        if($res->length === 1)
        {            
            $res->item(0)->nodeValue = $res->item(0)->nodeValue + 1;
            return true;
        }
         
        return false;
            

    }
} //end class

?>
