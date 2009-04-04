<?php
/**
 * -File        Parameter.Handler.php
 * -Copyright   Savonix Corporation
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 * This class is the parameter handler.
 * It allows modification or additon of Flow variables
 * from within the sitemap file
 *
 * @package     Nexista
 * @subpackage  Handlers
 */

class Nexista_ParameterHandler
{

    /**
     * Call this function to activate the processor
     *
     * @param   string      variable name
     * @param   string      variable value to assign
     * @return  boolean     success
     */

    public function process($name, $value)
    {

        $res = Nexista_Flow::find($name);

        //if var exists, set the new value
        if ($res->length === 1) {
            $res->item(0)->nodeValue = $value;
        } elseif ($res->length === 0) {
            //create new var/value
            Nexista_Flow::add($name, $value);
        }
        //more than one match exists
        else
            return false;
        return true;
    }

}
?>