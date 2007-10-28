<?php
/*
 * -File        true.builder.php
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

class TrueBuilder extends Builder
{

    /**
     * Returns start code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Builder::getCode()
     */

    public function getCodeStart()
    {
        $code[] = '{';

        return implode(NX_BUILDER_LINEBREAK, $code);

    }

    /**
     * Returns end code for this tag.
     * @return   string Final code to insert in gate
     * @see      Builder::getCode()
     */

    public function getCodeEnd()
    {

        $code[] = '}';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }

}

?>