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
        'var1' => '', //required
        'var2' => '' //required
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res1 = Nexista_Flow::find($this->params['var1']);
		$res2 = Nexista_Flow::find($this->params['var2']);
        if($res1->length === 1)
        {
            $res1->item(0)->nodeValue = $res1->item(0)->nodeValue.$res2->item(0)->nodeValue;
            return true;
        }

        return false;

    }
} //end class

?>