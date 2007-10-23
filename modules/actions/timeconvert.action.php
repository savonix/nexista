<?php
/*
 * -File        timeconvertfilter.php - Wed Aug 14 14:34:06 MST 2002
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004, Savonix Corporation
 * -Author      Albert Lash <>
 */


/**
 * This filter converts time formats.
 *
 */

class TimeConvertAction extends Action
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
        $hours = Path::get($this->params['hours'], "flow");
        $minutes = Path::get($this->params['minutes'], "flow");
        $am_pm = Path::get($this->params['am_pm'], "flow");
		$when=$this->params['when'];
		
        if($am_pm=="PM" && $hours!="12") { 
			$hours=$hours + 12;
		}
		if($am_pm=="AM" && $hours=="12") { 
			$hours="00";
		}
		
        $iso = "$hours:$minutes:00";
		$when = $when."_iso_time";
		Flow::add($when, $iso);

        return true;
    
    }
} //end class

?>

