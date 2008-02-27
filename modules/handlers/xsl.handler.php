<?php
/**
* File          xsl.handler.php
* License       LGPL (http://www.gnu.org/copyleft/lesser.html)
* Copyright     2002-2007, Nexista
* Author        joshua savage
* Author        Albert Lash
*/

/**
 * @package     Nexista
 * @subpackage  Handlers
 * @author      Joshua Savage
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
        
        // The following can be used with the NYT xslt cache.
        // Uncomment to use xsltcache: 
        
        $use_xslt_cache = "yses";
        $xslt_cache_dir = PROJECT_ROOT."/cache/".SERVER_NAME."/".APP_NAME."/xsl/";
        if(!is_dir($xslt_cache_dir)) { 
            mkdir($xslt_cache_dir);
        }
        $tmpfile = $xslt_cache_dir.basename($xslfile);
        if(!is_file($tmpfile) || $use_xslt_cache!="yes") {   
            $xsl = new DomDocument;
            $xsl->substituteEntities = true;
            
            // This is not good, what's the best way to deal with it?
            // Could put it somewhere else, config.xml?
            $xslfilecontents = 
'<!DOCTYPE xslt [
<!ENTITY nx_project_xsl "'.PROJECT_ROOT.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'xsl'.DIRECTORY_SEPARATOR.'">
<!ENTITY nx_app_xsl "'.NX_PATH_APPS.'templates'.DIRECTORY_SEPARATOR.'xsl'.DIRECTORY_SEPARATOR.'"> ]>';

            if(!is_file($xslfile)) { 
                Error::init('XSL Handler - Error processing XSL file - it is unavailable: '.$xslfile, NX_ERROR_FATAL);
            }
            $xslfilecontents .= file_get_contents($xslfile);
            
            $xsl->loadXML($xslfilecontents); 
            
            $xslHandler = new XsltProcessor;
            $xslHandler->importStyleSheet($xsl); 
            
            if($use_xslt_cache=="yes") { 
                $xsl->save($tmpfile);
            }
        }
        if($use_xslt_cache=="yes") { 
            $xslHandler = new xsltCache;
            $xslHandler->importStyleSheet($tmpfile); 
        }
        
        $output = $xslHandler->transformToXML($flow->flowDocument); 
      
        if($output === false)
        {
            Error::init('XSL Handler - Error processing XSL file: '.$xslfile, NX_ERROR_FATAL);
        }

        return $output;
    }

} // end class
?>