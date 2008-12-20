<?php
/**
 * -File        number_format.action.php
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


class Nexista_Number_FormatAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     * @access  private
     */

    var $params = array(
		'number' => '',
        'decimals' => '2',
		'dec_point' => '.',
		'thousands_sep' => ''
        );


    /**
     * Applies filter
     *
     * @return  boolean success
     * @access  private
     */

    function main()
    {
        $xpath = $this->params['number'];
        $node = Nexista_Flow::find($xpath);
        $decimals = Nexista_Path::get($this->params['decimals']);
        $dec_point = Nexista_Path::get($this->params['dec_point']);
        $thousands_sep = Nexista_Path::get($this->params['thousands_sep']);

        foreach($node as $item) {
            $item->nodeValue = number_format($item->nodeValue, $decimals, $dec_point, $thousands_sep);
        }

    }
} //end class

?>