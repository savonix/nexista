<?php
/*
 * -File        $Id: gate.action.php,v 1.1 2005/04/29 18:14:58 amadeus Exp $
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage, 
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage <>
 */
 
/**
 * This method accepts a gate name to redirect to and a boolean true/false for 
 * session continuity
 * If true, the session id will be tagged on the url to allow for cross domain sessions.
 * 
 * @package     Nexista
 * @subpackage  Actions
 */

class gateAction extends Action
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
        $destination = $_SERVER['PHP_SELF'].'?'.NX_ID.'='.Path::parseInlineFlow($this->params['url']);
        $appendSession = isset($this->params['session'])?$this->params['session']:null;
        

        if($appendSession)
            $destination .=  '&'.SESSID;
        
        session_write_close();

        header('Location: '.$destination);
        exit();
        
    }



} //end class

?>
