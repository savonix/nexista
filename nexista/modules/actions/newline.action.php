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

class newlineAction extends Action
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
        $var = Path::parseInlineFlow($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;

        //write new data to Flow
        $var->textContent = nl2br($var->textContent);
        return true;
    }


} //end class

?>
