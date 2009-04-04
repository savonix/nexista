<?php
/**
 * -File        Case.Builder.php
 * -Copyright   Savonix Corporation
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

class Nexista_CaseBuilder extends Nexista_Builder
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
        $code[] = 'case '.$path->get($this->action->getAttribute('value'),
            'string', JOIN_DOUBLE_QUOTE).':';
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
        $code[] = 'break;';
        return implode(NX_BUILDER_LINEBREAK, $code);
    }
}
?>