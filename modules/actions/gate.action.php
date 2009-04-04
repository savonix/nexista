<?php
/**
 * -File        Gate.Action.php
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
 * This method accepts a gate name to redirect to and a boolean true/false for
 * session continuity
 * If true, the session id will be tagged on the url to allow for cross domain
 * sessions.
 * 
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_gateAction extends Nexista_Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'url' => '',        //required - gate to redirect to
        'session' => ''     //optional - true to append sessionID on url
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        $destination = $_SERVER['PHP_SELF'].'?'.NX_ID.'='.Nexista_Path::parseInlineFlow($this->params['url']);
        $appendSession = isset($this->params['session'])?$this->params['session']:null;

        if($appendSession)
            $destination .=  '&'.SESSID;

        session_write_close();

        header('Location: '.$destination);
        exit();

    }



} //end class

?>