<?php
/**
 * -File        Plugin.Handler.php
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
 * This class is exactly like actions, but for application specific plugins.
 *
 * @package     Nexista
 * @subpackage  Handlers
 */

class Nexista_PluginHandler
{

    /**
     * Accepts an xml list of items and actions them according
     * to passed criteria
     *
     * This function will action a number of data fields as
     * described in a action xml file. Actions modify the data
     * in some way such as strip html, nl2br, etc..
     *
     *
     * @param   string      the name of the xml data file
     * @return  boolean     false if any field failed
     * @see
     */
    public function process($src)
    {
        //load descriptor file and parse
        $xml = simplexml_load_file($src);

        //parse through each node and process
        foreach ($xml->children() as $action) {
        self::processItem((string)$action['type'], (string)$action['params']);
        }

        return true;

    }


    /**
     * This function will apply one action to the source provided
     *
     * @param   string      tag name of flow value to change.
     * @param   string      the type of action
     */


    static public function processItem($type, $params)
    {

        //load the action module file based on $type
        require_once(NX_PATH_CORE . "action.php");

        //load the action module file based on $type
        require_once(NX_PATH_PLUGINS . trim(strtolower($type)) . ".plugin.php");

        //get the action parameters
        $params = explode(',', $params);

        //build the class name to load
        $classname = 'Nexista_' . trim(ucfirst($type)) . "Action";

        $action = new $classname();

        if (!$action->process($params)) {
            return false;
        }

        return true;
    }

}

?>