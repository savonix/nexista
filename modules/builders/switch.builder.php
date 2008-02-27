<?php
/*
 * -File        switch.builder.php
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

class Nexista_SwitchBuilder extends Nexista_Builder
{

    /**
     * Returns start code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Nexista_Builder::getCode()
     */

    public function getCodeStart()
    {
        $path = new Nexista_PathBuilder();
        $code[] = 'switch('.$path->get($this->action->getAttribute('name'), 'flow', JOIN_DOUBLE_QUOTE).') {';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }

}

?>