<?php
/*
 * -File        timeconvertfilter.php - Wed Aug 14 14:34:06 MST 2002
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2004, Savonix Corporation
 * -Author      Albert Lash <>
 */


/**
 * This filter conects variables from flow into a single variable in SQL timestamp format.
 *
 */

class DateConnectAction extends Action
{


    /**
     * Function parameter array
     *
     * @var array   
     * @access  private
     */
    
    var $params = array(
		'year' => '', 
        'month' => '', 
		'day' => '', 
		'name' => '' // name of new var
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
        $year = Path::get($this->params['year'],"flow");
        $month = Path::get($this->params['month'],"flow");
		$day = Path::get($this->params['day'],"flow");
		$name = $this->params['name'];
		
		$mydate = "$year-$month-$day 01:01:01";
		Flow::add($name, $mydate);

        return true;
    
    }
} //end class

?>
