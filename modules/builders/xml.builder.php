<?php
/*
 * -File        xml.builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
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

class Nexista_XmlBuilder extends Nexista_Builder
{


    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Nexista_Config::get('./path/handlers').'xml.handler.php';

        return $req;
    }


    /**
     * Returns start code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Nexista_Builder::getCode()
     */

    public function getCodeStart()
    {
        $path = new Nexista_PathBuilder();

        $params = "'".$this->action->getAttribute('src')."'";
        if($this->action->hasAttribute('parent')) { 
			$params .= ",".$this->action->getAttribute('parent');
		}
        $code[] = $this->addErrorHandler('Nexista_XmlHandler::process('.$params.')');
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>