<?php
/*
 * -File        validate.builder.php
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
 * @package     Nexista
 * @subpackage  Builders
 */

class ValidateBuilder extends Builder
{
    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Config::get('./path/handlers').'validator.handler.php';

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
        $code[] = $this->addErrorHandler("ValidatorHandler::process(".
            $path->get(NX_PATH_APPS.$this->action->getAttribute('src'), 'string', JOIN_NONE).",\$result)");
        $code[] = 'if($result)';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>