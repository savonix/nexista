<?php
/**
 * -File        Xsl.Handler.php
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
class Nexista_XslHandler
{

    /**
     * process xsl template with Flow xml
     *
     * @param   string      xsl source file
     * @return  string      xsl transformation output
     */

    public function process($xslfile)
    {

        $flow = Nexista_Flow::Singleton();

        // The following can be used with the NYT xslt cache.

        $use_xslt_cache = "yes";
        if (!is_file($xslfile)) {
            Nexista_Error::init('XSL Handler - Error processing XSL file,
                it is unavailable: '.$xslfile, NX_ERROR_FATAL);
        }
        if ($use_xslt_cache!="yes" || !class_exists('xsltCache')) {
            $xsl = new DomDocument('1.0', 'UTF-8');
            $xsl->substituteEntities = false;
            $xsl->resolveExternals = false;
            $xslfilecontents .= file_get_contents($xslfile);
            $xsl->loadXML($xslfilecontents);
            $xsl->documentURI = $xslfile;
            $xslHandler = new XsltProcessor;
            $xslHandler->importStyleSheet($xsl);
            if (4==3 && function_exists('setProfiling')) {
                $xslHandler->setProfiling("/tmp/xslt-profiling.txt");
            }
        } else {
            $xslHandler = new xsltCache;
            $xslHandler->importStyleSheet($xslfile);
        }

        $output = $xslHandler->transformToXML($flow->flowDocument);

        if ($output === false) {
            Nexista_Error::init('XSL Handler - Error processing XSL file: '.$xslfile, NX_ERROR_FATAL);
        }

        return $output;
    }

} // end class
?>