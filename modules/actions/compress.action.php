<?php
/**
 * -File        Compress.Action.php
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
 * This action applies gzip compress to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_CompressAction extends Nexista_Action
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
			$res->item(0)->nodeValue = base64_encode(gzcompress($text,6));
            return true;
        }

        return false;

    }
} //end class

?>