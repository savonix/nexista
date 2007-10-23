<?php
/*
 * -File        set.builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004-2007, Nexista
 * -Author      joshua savage
 * -Author      Albert Lash
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

class SetBuilder extends Builder
{
    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Config::get('./path/handlers').'parameter.handler.php';

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
        $code[] = $this->addErrorHandler("ParameterHandler::process('".
			$this->action->getAttribute('name')."',".
			$path->get($this->action->getAttribute('value'), 'string', JOIN_SINGLE_QUOTE).")");
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>