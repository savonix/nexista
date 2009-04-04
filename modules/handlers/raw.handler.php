<?php
/**
 * -File        Raw.Handler.php
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
 * This class adds a raw files (plain text, html, whatever)
 * to the display stream
 *
 * @package     Nexista
 * @subpackage  Handlers
 */

class Nexista_RawHandler
{

    /**
     * Processes raw data files and throws them in stream
     *
     *
     * @param   string      name of file
     * @param   string      file content, returned by ref
     * @return  boolean     function success
     */

    public function process($src, &$result)
    {

        if (!$result = file_get_contents($src)) {
            return false;
        }

        return true;
    }

} //end class
?>