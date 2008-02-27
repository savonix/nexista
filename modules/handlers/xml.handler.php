<?php
/*
 * -File        xml.handler.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002-2007, Nexista
 * -Author      joshua savage
 * -Author		Albert Lash
 */

/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage
 */
 
/**
 * This class is the xml handler. It will take an xml file
 * and insert the data into Flow for access from scripts and xsl
 * modules.
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class XmlHandler
{
    /**
     * Call this function to activate the xml handler
     *
     *
     * @param       string  the name of the xml file to process
     * @param       string - path of parent. default to root
     * @return      boolean success
     */

    public function process($src, $parent = false)
    {
        //load xml file
        $doc = new DOMDocument();
        $doc->load($src);
        //Added this in Jan 2008, need to watch for any potential problems.
        $doc->xinclude();
 
        $flow = Flow::singleton();
        
        //import new doc into flow recursively
        $new = $flow->flowDocument->importNode($doc->documentElement,1);
        
        //append to parent if called for
        if($parent)
        {
            $res = Flow::find($parent);
            if($res->length > 0)
            {
                $parent = $res->item(0);
                $parent->appendChild($new);
            }
            else
                return false;
        }
        //goes in root
        else
            $flow->root->appendChild($new);
        
        return true;

    }

} //end class
?>