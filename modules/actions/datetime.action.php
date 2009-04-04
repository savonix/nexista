<?php
/**
 * -File        Datetime.Action.php
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