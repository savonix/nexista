<?php
/*
 * -File        xml.builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004-2007, Nexista
 * -Author      joshua savage
 */


/**
 * @package     Nexista
 * @subpackage  Builders
 * @author      Joshua Savage <>
 */
 
/**
 * This class handles the tag by the same name in the sitemap building process
 *
 * @package     Nexista
 * @subpackage  Builders
 */

class XmlBuilder extends Builder
{


    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Config::get('./path/handlers').'xml.handler.php';

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
        $path = new PathBuilder();
		$params = $path->get(NX_PATH_APPS.$this->action->getAttribute('src'), 'string', JOIN_SINGLE_QUOTE);
		if($this->action->hasAttribute('parent')) { 
			$params .= ",".$this->action->getAttribute('parent');
		}
        $code[] = $this->addErrorHandler('XmlHandler::process('.$params.')');
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>