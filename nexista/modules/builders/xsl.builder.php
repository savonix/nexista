<?php
/*
 * -File        xsl.builder.php
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


class Nexista_XslBuilder extends Nexista_Builder
{


    /**
     * Returns array of required files to insert in require_once fields
     *
     * @return    array Required files
     * @see
     */

    public function getRequired()
    {
        $req[] = Nexista_Config::get('./path/handlers').'xsl.handler.php';

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
        $code[] = '$xsl =& new Nexista_XslHandler();';
$code[] = '$output .= $xsl->process('.$path->get(NX_PATH_APPS.$this->action->getAttribute('src'), 'string', JOIN_SINGLE_QUOTE).');';
        // Want to delete as of Oct 2007:   
        //$code[] = '$output .= $xsl->process('.$path->get($this->action->getAttribute('src'), 'string', JOIN_SINGLE_QUOTE).');';
     
        return implode(NX_BUILDER_LINEBREAK, $code);

    }

}

?>