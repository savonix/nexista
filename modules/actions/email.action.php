<?php
/*
 * -File        email.plugin.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Savonix Corporation
 * -Author		Albert Lash
 */

/**
 * @package     Nexista Auth
 * @subpackage  Plugins
 * @author      Albert Lash
 */
 
/**
 * This action sends an email and preferrably uses the PEAR Net_SMTP package.
 *
 * @package     Nexista Auth
 * @subpackage  Plugins
 */

class Nexista_EmailAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'recipient' => '', //required - 
        'sender' => '', //required - 
        'subject' => '', //optional - 
        'body' => '', //required - 
        'host' => '',
        'port' => '',
        'authentication' => ''
        
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        $recipient = Nexista_Path::parseInlineFlow($this->params['recipient']);
        $sender = $this->params['sender'];
        $subject = "Subject: ".$this->params['subject']."\n";
        $body = $this->params['body'];
        $host = $this->params['host'];

        if(require 'Net/SMTP.php') { 

            $smtp = new Net_SMTP($host);
            $e = $smtp->connect();
            $smtp->mailFrom($sender);
            
            if(is_array($recipient)) { 
                foreach ($recipient as $to) {
                    $smtp->rcptTo($to);
                }
            } else {
                $res = $smtp->rcptTo($recipient);
            }
            $smtp->data($subject . "\r\n" . $body);
            $smtp->disconnect();
            
        } else { 
            // try using mail()

        }

    }
} //end class

?>