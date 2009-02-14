<?php
/**
 * -File        Log.Action.php
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
 *
 * This action uses PEAR Log to enable logging.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_logAction extends Nexista_Action
{


    /**
     * Function parameter array
     *
     * @var     array
     */

    protected  $params = array(
        'logging' => '',   //required - is logging on?
        'lfile' => '',   //required - file to log to
        'code'    => '',   //required - 3 letter action code
        'log_msg'  => ''  //optional - log message
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        $logging = $this->params['logging'];
        if ($logging === 0 || !$logging) {
            return true;
        }
        $lfile   = $this->params['lfile'];
        $code    = $this->params['code'];
        $log_msg = $this->params['log_msg'];

        require_once 'Log.php';

        $file = &Log::factory('file', $lfile, $code);
        $file->log($log_msg);
        return true;
    }



} //end class

?>