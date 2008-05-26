<?php
/*
 * -File        strip.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Albert Lash
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
 */
 
/**
 * This action strips unwanted characters from a string.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_stripAction extends Nexista_Action
{

    /* NOTE: This action is deprecated, use sed.action.php instead */
    
    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => '',        //required - flow var
        'chars' => ''   //optional - array of chars to strip
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res = Nexista_Flow::find($this->params['var']);
        if(!empty($res)) {
            $chars = Nexista_Flow::getByPath($this->params['chars']);
            $res->item(0)->nodeValue = str_replace($chars,"",$res->item(0)->nodeValue);

            return true;
        } else {
            return false;
        }
    }
} //end class
?>