<?php
/**
 * -File        If.Builder.php
 * -Copyright   Nexista
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
 * This class handles the tag by the same name in the sitemap building process
 *
 * @package     Nexista
 * @subpackage  Builders
 */

class Nexista_IfBuilder extends Nexista_Builder
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
        $name = $this->action->getAttribute('name');
        //$code[] = 'if ('.$path->get($name, 'flow', JOIN_NONE).')';
        $code[] = '$if_test = '.$path->get($name, 'flow', JOIN_NONE).';';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
    /**
     * Returns end code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Nexista_Builder::getCode()
     */

    public function getCodeEnd()
    {

        $code[] = '$if_test = false;';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }
}

?>