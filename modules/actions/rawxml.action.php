<?php
/**
 * -File        RawXml.Action.php
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
 * This action pulls out a var from Flow and reinserts it raw. Meaning nothing
 * gets escaped to prevent the Flow xml from breaking. This is used to take an xml string
 * say from the database, and merge it with Flow so that the final data will be accessible as
 * xml instead of an escaped string.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_RawXmlAction extends Nexista_Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => '',     //required - flow var
        'replace' => '' //optional - remove existing xml string?
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

        $var = Nexista_Flow::find($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;
        $res = $var->item(0);
        $xmlString = $res->textContent;

         //load xml string
        $doc = new DOMDocument();
        $doc->loadXML($xmlString);

        $flow = Nexista_Flow::singleton('Nexista_Flow');

        //import new doc into flow recursively
        $new = $flow->flowDocument->importNode($doc->documentElement,1);

        $replace = $this->params['replace'];
        if($replace == "true") {
            $res->nodeValue = "";
        }
        //append back to node as parsed xml now
        $res->appendChild($new);
        //echo $flow->outputXml($flow->root);
        //exit;
        return true;
    }


} //end class

?>