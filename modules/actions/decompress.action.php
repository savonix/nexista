<?php
/**
 * -File        Decompress.Action.php
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
 * This action applies gzip compress to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_DecompressAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var' => '' //required - name of flow var to gzip
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {

		$res = Nexista_Flow::find($this->params['var']);
        if($res->length === 1)
        {
			$text = $res->item(0)->nodeValue;
			$res->item(0)->nodeValue = htmlspecialchars(gzuncompress(base64_decode($text)));
            return true;
        }

        return false;

    }
} //end class

?>