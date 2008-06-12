<?php
/**
 * -File        Concat.Action.php
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
 * This action concatenates params
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_ConcatAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var1' => '', //required - first part of concatenation
        'var2' => '', //required - second part of concatenation
        'var3' => '', //required - existing xpath node or new node name
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res1 = Nexista_Path::parseInlineFlow($this->params['var1']);
		$res2 = Nexista_Path::parseInlineFlow($this->params['var2']);
		$res3 = Nexista_Flow::find($this->params['var3']);
        
        if ($res3->length === 1) {
            // there is a node, replace it
            $res3->item(0)->nodeValue = $res1 . $res2;
            return true;
        } else {
            // create a new node
            $new_node = $this->params['var3'];
            Nexista_Flow::add($new_node, $res1 . $res2);
        }

        return false;

    }
} //end class

?>