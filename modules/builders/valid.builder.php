<?php
/*
 * -File        $Id: valid.builder.php,v 1.3 2005/02/08 21:50:39 amadeus Exp $
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage, 
 */


/**
 * @package     Nexista
 * @subpackage  Builders
 * @author      Joshua Savage <>
 */
 
/**
 * This class handles the tag by the same name in the sitemap building process
 *
 * @package     Nexista
 * @subpackage  Builders
 */

class Nexista_ValidBuilder extends Nexista_Builder
{
   /**
     * Returns start code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Nexista_Builder::getCode()
     */

    public function getCodeStart()
    {

        $code[] = '{';


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

        $code[] = '}';
        return implode(NX_BUILDER_LINEBREAK, $code);

    }


}

?>