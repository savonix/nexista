<?php
/*
 * -File        raw.builder.php
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

class Nexista_RawBuilder extends Nexista_Builder
{

    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Nexista_Config::get('./path/handlers').'raw.handler.php';

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
        $src = $this->action->getAttribute('src');
        $code[] = $this->addErrorHandler("Nexista_RawHandler::process('".$src."', \$rawdata)", '', 'FATAL');
        $code[] = '$output .= $rawdata;';

        return implode(NX_BUILDER_LINEBREAK, $code);
    }
}
?>