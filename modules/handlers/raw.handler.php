<?php
/*
 * -File        raw.handler.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage
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

        if(!$result = file_get_contents($src))
        {
            return false;
        }

        return true;
    }

} //end class
?>