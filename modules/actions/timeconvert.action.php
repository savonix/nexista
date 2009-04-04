<?php
/**
 * -File        TimeConvert.Action.php
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
 * This filter converts time formats.
 *
 * @package     Nexista
 * @subpackage  Actions
 *
 */

class Nexista_TimeConvertAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     * @access  private
     */

    var $params = array(
        'hours' => '', //required - hours of flow var to convert
		'minutes' => '', // defaults to zero
		'am_pm' => '', // required am/pm specifier
		'when' => '' // required - open or close....
        );

    
    /**
     * Applies filter
     *
     * @return  boolean success
     * @access  private
     */
    
    function main()
    {

		// Right now this filter only converts from 12 to 24 hour time formats.
        $hours = Nexista_Path::get($this->params['hours'], "flow");
        $minutes = Nexista_Path::get($this->params['minutes'], "flow");
        $am_pm = Nexista_Path::get($this->params['am_pm'], "flow");
		$when=$this->params['when'];

        if($am_pm=="PM" && $hours!="12") {
			$hours=$hours + 12;
		}
		if($am_pm=="AM" && $hours=="12") {
			$hours="00";
		}

        $iso = "$hours:$minutes:00";
		$when = $when."_iso_time";
		Nexista_Flow::add($when, $iso);

        return true;

    }
} //end class

?>