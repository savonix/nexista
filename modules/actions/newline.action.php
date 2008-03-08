<?php
/*
 * -File        newline.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author  Joshua Savage
 */
 
/**
 * This action applies nl2br() to the data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_newlineAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => ''     //required - flow var
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        /*
                $var = Nexista_Flow::getByPath($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;

        $name = $this->params['name'];
        //write new data to Flow
        $new_text = nl2br($var);
        Nexista_Flow::add($name,$new_text);
        return true;
        */
        $var = Nexista_Flow::find($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;

        //write new data to Flow
        $var->item(0)->nodeValue = nl2br($var->item(0)->nodeValue);
        return true;
    }


} //end class

?>