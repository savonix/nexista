<?php
/**
 * -File        DateConnect.Action.php
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
 * This action connects variables from flow into a single variable in 
 * SQL timestamp format.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_DateConnectAction extends Nexista_Action
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
        $year  = Nexista_Path::get($this->params['year'], 'flow');
        $month = Nexista_Path::get($this->params['month'], 'flow');
		$day   = Nexista_Path::get($this->params['day'], 'flow');
		$name  = $this->params['name'];

		$mydate = "$year-$month-$day 01:01:01";
		Nexista_Flow::add($name, $mydate);

        return true;

    }
} //end class

?>