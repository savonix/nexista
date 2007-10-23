<?php
/*
 * -File        Action.php - Fir Jul 8 08:00:00 EST 2005
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2005, Savonix
 * -Author      Albert Lash <>
 */


/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash <>
 */
 
/**
 * This action applies crc32 hash to data
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Encrypt_KeyAction extends Action
{


    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'pw' => ''
		);


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$my_pw = Flow::find($this->params['pw']);        
		$my_key=$_SESSION['NX_AUTH']['key'];
		
        if($my_pw->length === 1)
        {            
			// set IV
			if(function_exists('mcrypt_get_iv_size')) { 
				$iv_size = mcrypt_get_iv_size(MCRYPT_XTEA, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
			} else { 
				$iv="";
			}
			
			// ENCRYPT KEY
$key = base64_encode(mcrypt_encrypt(MCRYPT_3DES, $my_pw->item(0)->nodeValue, $my_key, MCRYPT_MODE_ECB, $iv));
			Flow::add("my_new_key", $key);
			
			
			
            return true;
        }
         
        return false;
            

    }
} //end class

?>
