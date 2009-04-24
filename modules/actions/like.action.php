<?php
/**
 * -File        Like.Action.php
 * -Copyright   Savonix Corporation
 * -Author      Joshua Savage
 * -Author      Albert Lash
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */
 
 
/**
 * This action adds %'s to data for using a LIKE SQL query
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_LikeAction extends Nexista_Action
{

    /* Note: this file is deprecated, use the concatenation action */
    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var' => '' //required - name of flow var to edit
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res = Nexista_Flow::find($this->params['var']);
        if($res->length === 1)
        {
            $res->item(0)->nodeValue = "%" . $res->item(0)->nodeValue . "%";
            return true;
        }

        return false;

    }
} //end class

?>