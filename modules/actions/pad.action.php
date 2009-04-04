<?php
/**
 * -File        Pad.Action.php
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
 * This action concatenates params
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_PadAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var1' => '', //required - string to pad
        'var2' => '', //required - length
        'var3' => ' ', //required - string to pad with
        'var4' => '', //optional - type of pad
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

        $xpath = $this->params['var1'];
        $node = Nexista_Flow::find($xpath);

        $length = $this->params['var2'];
		$pad = Nexista_Path::parseInlineFlow($this->params['var3']);
        $type = $this->params['var4'];

        foreach($node as $str) {
            $str->nodeValue = str_pad($str->nodeValue, $length, $pad,STR_PAD_RIGHT);
        }

    }
} //end class

?>