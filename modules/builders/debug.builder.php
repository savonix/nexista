<?php
/*
 * -File        debug.builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004-2007, Nexista
 * -Author      joshua savage
 */


/**
 * @package     Nexista
 * @subpackage  Builders
 * @author      Joshua Savage 
 */
 
/**
 * This class handles the tag by the same name in the sitemap building process
 *
 * @package     Nexista
 * @subpackage  Builders
 */

class DebugBuilder extends Builder
{

    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Config::get('./path/handlers').'xsl.handler.php';

        return $req;
    }

    
    /**
     * Returns start code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Builder::getCode()
     */

    public function getCodeStart()
    {
               
        //see if we want to dump xml
        if($this->action->getAttribute('dump') === 'true' )
        {
            $this->handler = new XsltProcessor();
            $this->xml = new DomDocument; // from /ext/dom
            $this->xsl = new DomDocument;
        
            $code[] = '$debugXsl = new XsltProcessor();';
            $code[] = '$xsl = new DomDocument;';
            $code[] = '$xsl->load("'.Config::get('./path/base').'kernel/xsl/flow.xsl");';
            $code[] = '$debugXsl->importStyleSheet($xsl);';
            $code[] = '$flow = Flow::singleton();';
            $code[] = '$debugOutput = $debugXsl->transformToXML($flow->flowDocument);';
          }

        //add a little text blurb do we?
        $text = $this->action->getAttribute('text');
        $code[] = !empty($text) ? "echo '".$text."';":null;

        //stop code?        
        $code[] = $this->action->hasAttribute('die') ? "return \$output; exit;":null;

        return implode(NX_BUILDER_LINEBREAK, $code);

    }

}

?>