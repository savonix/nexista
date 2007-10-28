<?php
/*
 * -File        parameterhandler.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage 
 */

/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage
 */
 
/**
 * This class is the parameter handler.
 * It allows modification or additon of Flow variables
 * from within the sitemap file
 *
 * @package     Nexista
 * @subpackage  Handlers
 */

class ParameterHandler
{

    /**
     * Call this function to activate the processor
     *
     *
     * @param   string      variable name
     * @param   string      variable value to assign
     * @return  boolean     success
     */

    public function process($name, $value)
    {

        $res = Flow::find($name);
        
        //if var exists, set the new value
        if($res->length === 1)
        {
            $res->item(0)->nodeValue = $value;
        }
        //create new var/value
        elseif($res->length === 0)
        {
            Flow::add($name, $value);
        }
        //more than one match exists
        else
            return false;
        return true;
    }

}
?>