<?php
/**
 * -File        Newline.Action.php
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
 * This action applies nl2br() to the data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_newlineAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'var' => ''     //required - flow var
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

        $xpath = $this->params['var'];
        $node = Nexista_Flow::find($xpath);
        foreach($node as $item) {
        //write new data to Flow
            $string = nl2br($item->nodeValue);
            $string = preg_replace("/(<pre>)(.*)(<\/pre>)/mes","'<PRE>'.str_replace('<br />','','$2').'$3'.'</PRE>'",$string);
            $item->nodeValue = htmlspecialchars($string);
        }
        return true;
    }


} //end class

?>
