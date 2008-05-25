<?php
/*
 * -File        redirect.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Albert Lash
 * -Author      joshua savage
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 */
 
/**
 * This method accepts a url and a boolean true/false for session continuity
 * If true, the session id will be tagged on the url to allow for cross domain sessions.
 * 
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_redirectAction extends Nexista_Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'url' => 'required',        //optional - url to redirect to. referer if not set
        'session' => ''     //optional - true to append sessionID on url
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        $destination = isset($this->params['url'])? Nexista_Path::parseInlineFlow($this->params['url']):null;
        $appendSession = isset($this->params['session'])?$this->params['session']:null;
        
        if(is_null($destination) and isset($_SERVER['HTTP_REFERER']))
            $destination = $_SERVER['HTTP_REFERER'];
              
        if($appendSession)
            $destination .=  '&'.SESSID;
        
        session_write_close();

        header('Location: '.$destination);
        exit();
        
    }

} //end class

?>