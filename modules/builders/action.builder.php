<?php
/*
 * -File        action.builder.php
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
 * @see         Builder
 * @package     Nexista
 * @subpackage  Builders
 */

class Nexista_ActionBuilder extends Nexista_Builder
{

    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Nexista_Config::get('./path/handlers').'action.handler.php';
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
        if($this->action->hasAttribute('src'))
        {
            $code[] = $this->addErrorHandler("Nexista_ActionHandler::process(".
                $path->get($this->action->getAttribute('src'), 'string', JOIN_DOUBLE_QUOTE).", true)");

        }
        else
        {
            if($this->action->hasAttribute('params'))
            {
                $code[] = $this->addErrorHandler("Nexista_ActionHandler::processItem('".
                    $this->action->getAttribute('type')."',".$path->get($this->action->getAttribute('params'), 'string', JOIN_DOUBLE_QUOTE).", true)");
            } else {
                $code[] = $this->addErrorHandler("Nexista_ActionHandler::processItem('".
                    $this->action->getAttribute('type')."', true)");
            }
        }
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>