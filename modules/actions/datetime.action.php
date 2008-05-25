<?php
/*
 * -File        datetime.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      Albert Lash
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
 */
 
/**
 * This action is a multi-function date and time converter.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_DatetimeAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array   
     * @access  private
     */
    
    var $params = array(
		'source_datetime' => '',
        'source_format' => '', 
		'target_format' => '', 
		'timezone_offset' => ''
        );


    /**
     * Applies filter
     *
     * @return  boolean success
     * @access  private
     */
    
    function main()
    {
        $xpath = $this->params['source_datetime'];
        $node = Nexista_Flow::find($xpath);
        $sformat = Nexista_Path::get($this->params['source_format']);
        $tformat = Nexista_Path::get($this->params['target_format']);
        $tz = Nexista_Path::get($this->params['timezone_offset']);
        $tz = $tz * 3600;
        foreach($node as $item) {
            if($sformat == 'unix_epoch') { 
                $item->nodeValue = gmdate($tformat, $item->nodeValue + ($tz));
            } else { 
                $unix_epoch_str = strtotime($item->nodeValue);
                $item->nodeValue = gmdate($tformat, $unix_epoch_str + ($tz));
            }
        }

    }
} //end class

?>