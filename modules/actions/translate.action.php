<?php
/**
 * -File        Translate.Action.php
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
 * This action escapes all those evil characters based on defined translation table.
 * The default is HTML_ENTITIES. We used strtr here instead of htmlentities to allow
 * for reverse as well as custom tables which will come later.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_translateAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => '',        //required - flow var
        'transtbl' => '',   //optional - translation table used for encoding
        'reverse' => ''     //optional - whether to apply in reverse manner (decode)
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

        $var = Nexista_Flow::find($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;
        switch(strtoupper($this->params['transtbl']))
        {
            case 'HTML_ENTITIES':
                 $trans_tbl = get_html_translation_table (HTML_ENTITIES);
                 break;

            //for dealing with non xsl safe chars
            case 'XSL_ENTITIES';

                 $trans_tbl = array(
                    '&nbsp;' => '&#160;',

                );
                break;

            default:

                $trans_tbl = get_html_translation_table (HTML_ENTITIES);
                 break;

        }

        if(!empty($this->params['reverse']) && ($this->params['reverse'] === 'true'))
        {
             $trans_tbl = array_flip ($trans_tbl);
        }

        //write new data to Flow
        $var->item(0)->nodeValue = strtr($var->item(0)->nodeValue, $trans_tbl);
        return true;
    }


} //end class

?>