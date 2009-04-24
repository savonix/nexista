<?php
/**
 * -File        Script.Handler.php
 * -Copyright   Savonix Corporation
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 * This class is the php handler.
 * It basically reads the php file and processes it
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class Nexista_ScriptHandler
{

    /**
     * Call this function to activate the processor
     *
     *
     * @param   string      php filename
     */

    public function process($src)
    {
        // include our code from the file
        include($src);
        return true;
    }

}
?>