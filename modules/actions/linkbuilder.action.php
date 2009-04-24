<?php
/**
 * -File        Linkbuilder.Action.php
 * -Copyright   Savonix Corporation
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


class Nexista_LinkbuilderAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     * @access  private
     */

    protected  $params = array(
        'ids' => '', //ids to build links for
        'param_array_xpath' => '' //node containing key value pairs of tokens to replace.
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        $ids_x = $this->params['ids'];
		$ids = Nexista_Flow::find($ids_x);
        $params = Nexista_Flow::getbypath($this->params['param_array_xpath']);

        foreach ($ids as $id)
        {
                $link = "";
                foreach ($params as $param)
                {
                    //$link .= $param['name']."=";
                    $myname = $param['name'];
                    if($myname=="key") {
                        $link .= $id->nodeValue."/";
                    } else {
                        $myval = Nexista_Flow::getbypath($param['value']);
                        $link .= $myval."/";
                    }
                }
                $id->nodeValue = $link;
        }
        return true;

    }
} //end class

?>