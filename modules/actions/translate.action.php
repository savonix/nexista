<?php
/*
 * -File        translate.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002-2007, Nexista
 * -Author      joshua savage
 * -Author      albert lash
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * This action escapes all those evil characters based on defined translation table.
 * The default is HTML_ENTITIES. We used strtr here instead of htmlentities to allow
 * for reverse as well as custom tables which will come later.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class translateAction extends Action
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

        $var = Path::get($this->params['var']);
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
        $var->textContent = strtr($var->textContent, $trans_tbl);
        return true;
    }


} //end class

?>
