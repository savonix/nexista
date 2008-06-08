<?php
/**
 * -File        Xml.Handler.php
 * -Copyright   Nexista
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
 * This class is the xml handler. It will take an xml file
 * and insert the data into Flow for access from scripts and xsl
 * modules.
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class Nexista_XmlHandler
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
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load($src);
        $doc->xinclude();
 
        $flow = Nexista_Flow::singleton();

        //import new doc into flow recursively
        $new = $flow->flowDocument->importNode($doc->documentElement, 1);

        //append to parent if called for
        if ($parent) {
            $res = Nexista_Flow::find($parent);
            if ($res->length > 0) {
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