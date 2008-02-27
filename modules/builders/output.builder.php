<?php
/*
 * -File        output.builder.php
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

class Nexista_OutputBuilder extends Nexista_Builder
{

    /**
     * Returns start code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Nexista_Builder::getCode()
     */

    public function getCodeStart()
    {
        $code[] = null;
        if($this->action->getAttribute('src') === 'eval')
        {   
            $code[] = '$flow = Nexista_Flow::singleton();';
            $code[] = '$flow->writeXmlStream($flow->outputXml());';
            $code[] = 'eval($output);';
        }

        return implode(NX_BUILDER_LINEBREAK, $code);
    }
}

?>