<?php
/**
 * -File        Xml.Builder.php
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
        // PathBuilder is currently needed here for translations
        // Though I'm not thrilled with how i18n is performing in that manner
        if(strpos($this->action->getAttribute('src'),'http://')!==false) {
            $params = "'".$this->action->getAttribute('src')."'";
        } else {
            $params = $path->get($this->action->getAttribute('src'), 
                'string', JOIN_SINGLE_QUOTE);
		}
        if($this->action->hasAttribute('parent')) { 
			$params .= ",".$this->action->getAttribute('parent');
		}
        $code[] = $this->addErrorHandler('Nexista_XmlHandler::process('.$params.')');
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>