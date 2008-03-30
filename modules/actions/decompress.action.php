<?php
/*
 * -File        decompress.action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2002, Nexista
 * -Author      joshua savage
 * -Author		Albert Lash
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Joshua Savage
 * @author      Albert Lash
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