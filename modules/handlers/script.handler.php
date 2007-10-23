<?php
/*
 * -File        scripthandler.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002-2007, Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage
 */
 
/**
 * This class is the php handler.
 * It basically reads the php file and processes it
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class ScriptHandler 
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
