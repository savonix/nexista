// <?php
/*
 * -File        escape.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * This action strips/translates text into db/xsl safe text.  It does not attempt to remove
 * html tags (see translateaction) except for tags with namespaces (i.e. <o:xxx>), but focuses
 * on dealing with weird characters, &nbsp; and other things that can break xsl.
 * The functionality of this action is similar to translateaction. We need to define which
 * does what.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_escapeAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => '',        //required - flow var
        'translate' => ''     //optional - true to strip char > 128, false to escape with char code
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

        
        $var = Nexista_Flow::getByPath($this->params['var']);
        if(is_null($var) or is_array($var))
            return false;
            
        $trans_array = array();
        for ($i=127; $i<255; $i++)
        {

            if($this->params['translate'] == 'true')
            {
                $trans_array[chr($i)] = "&#" . $i . ";";

            }
            else
            {
                $trans_array[chr($i)] = "";
            }

        }

        //also deal with xsl stuff
        $sterile = strtr($var->textContent, $trans_array);

        //strip tags with namespaces
        $sterile = preg_replace('~<([^>\"=\']*):(.*)>~mU','', $sterile);

        //write new data to Flow
        $var->textContent = $sterile;
        return true;
    }

} //end class

?>