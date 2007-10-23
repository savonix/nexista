<?php
/*
 * -File        $Id: rawxml.action.php,v 1.1 2005/04/29 18:14:58 amadeus Exp $
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage, 
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author  Joshua Savage <>
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

class RawXmlAction extends Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => ''     //required - flow var
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
      
        $var = Flow::find($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;
        $res = $var->item(0);
        $xmlString = $res->textContent;
        
         //load xml string
        $doc = new DOMDocument();
        $doc->loadXML($xmlString);
 
        $flow = Flow::singleton();
        
        //import new doc into flow recursively
        $new = $flow->flowDocument->importNode($doc->documentElement,1);
        
        //append back to node as parsed xml now
        $res->appendChild($new);
       
        
        return true;
    }


} //end class

?>
