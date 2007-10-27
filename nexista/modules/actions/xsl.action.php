<?php
/*
 * -File        $Id: xsl.action.php,v 1.2 2005/04/29 18:51:00 amadeus Exp $
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage, 
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage <>
 */
 
/**
 * This action applies an xsl stylesheet to an xml string.
 *
 * The class accepts 3 arguments. 1) the flow variable name
 * containing the xml string, 2) the filename/path of the xsl
 * stylesheet to apply (from NX_PATH_APP) and 3) and optional
 * string that will be used as root tag along with the adding of
 * xml headers. This would be used when a string has tags that that
 * should be transformed by a stylesheet but the string does not have
 * headers/root tags.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class XslAction extends Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'xml' => '',    //required - flow var
        'xsl' => ''    //required - xsl file        
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected function main()
    {

        
        $res = Path::get($this->params['xml'], 'string');
        if($res->length === 1)
        {
            $xml = $res->item(0)->nodeValue;
        
            $xslHandler = new XsltProcessor();
            $xsl = new DomDocument;
            $xsl->load($this->params['xsl']);
            $xml = new DomDocument;
            $xml->loadXML($this->params['xml']);
            $xslHandler->importStyleSheet($xsl); 
      
            $res->item(0)->nodeValue = $xslHandler->transformToXML($xml); 
       
            return true;
        }
        else
            return false;
    }


} //end class

?>
