<?php
/**
 * -File        Email.Action.php
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
        $sender = Nexista_Path::parseInlineFlow($this->params['sender']);
        $subject = "Subject: ".Nexista_Path::parseInlineFlow($this->params['subject'])."\n";
        $body = Nexista_Flow::getbypath($this->params['body']);
        $host = Nexista_Path::parseInlineFlow($this->params['host']);

        if(require 'Net/SMTP.php') {

            $smtp = new Net_SMTP($host);
            $e = $smtp->connect();
            $smtp->mailFrom($sender);

            $disclosed_recipients = "To: ";
            if(is_array($recipient)) {
                foreach ($recipient as $to) {
                    if (PEAR::isError($res = $smtp->rcptTo($to))) {
                        die("Unable to add recipient <$to>: " . $res->getMessage() . "\n");
                    }
                    $disclosed_recipients .= $to;
                }
            } else {

                if (PEAR::isError($res = $smtp->rcptTo($recipient))) {
                    die("Unable to add recipient <$recipient>: " . $res->getMessage() . "\n");
                }
                $disclosed_recipients .= $recipient;
            }
            $disclosed_recipients .= "\n";

            $headers = $disclosed_recipients . $subject;
            $smtp->data($headers . "\r\n" . $body);
            $smtp->disconnect();

        } else {
            // try using mail()

        }

    }
} //end class

?>