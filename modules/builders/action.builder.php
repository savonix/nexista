<?php
/*
 * -File        action.builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004, Nexista
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
 * @see         Builder
 * @package     Nexista
 * @subpackage  Builders
 */

class ActionBuilder extends Builder
{

    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Config::get('./path/handlers').'action.handler.php';
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
        if($this->action->hasAttribute('src'))
        {
            $code[] = $this->addErrorHandler("ActionHandler::process(".
                $path->get($this->action->getAttribute('src'), 'string', JOIN_DOUBLE_QUOTE).", true)");

        }
        else
        {
            $code[] = $this->addErrorHandler("ActionHandler::processItem('".
                $this->action->getAttribute('type')."','".$this->action->getAttribute('params')."')");
            if($this->action->hasAttribute('parent')) { 
                $params .= ",".$this->action->getAttribute('parent');
            }
        }
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>