<?php
/*
 * -File        xslhandler.php,v 1.2 2002/08/17 00:44:57 author Exp $
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage <>
 */


/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage <>
 */
 
/**
 * This class is reponsible for applying an xsl stylesheet to xml
 *
 * @package     Nexista
 * @subpackage  Handlers
 */
class XslHandler
{


    /**
     * process xsl template with Flow xml
     *
     * @param   string      xsl source file
     * @return  string      xsl transformation output
     */

    public function process($xslfile)
    {
        
        $flow = Flow::Singleton();
        
        $xslHandler = new XsltProcessor();
        $xsl = new DomDocument;
		$xsl->substituteEntities = true;
        $xslfilecontents = '<!DOCTYPE xslt [
<!ENTITY nx_project_xsl "'.PROJECT_ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'xsl'.DIRECTORY_SEPARATOR.'">
<!ENTITY nx_app_xsl "'.NX_PATH_APPS.'templates'.DIRECTORY_SEPARATOR.'xsl'.DIRECTORY_SEPARATOR.'"> ]>';
        if(!is_file($xslfile)) { 
            Error::init('XSL Handler - Error processing XSL file - it is unavailable: '.$xslfile, NX_ERROR_FATAL);
        }
        $xslfilecontents .= file_get_contents($xslfile);
        
        $xsl->loadXML($xslfilecontents);    

        $xslHandler->importStyleSheet($xsl); 
      
        $output = $xslHandler->transformToXML($flow->flowDocument); 
      
        if($output === false)
        {
            Error::init('XSL Handler - Error processing XSL file: '.$xslfile, NX_ERROR_FATAL);
        }

        return $output;
    }

} // end class
?>