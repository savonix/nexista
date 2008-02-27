<?php
/*
 * -File        Action.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   2005-2007, Savonix
 * -Author      Albert Lash
 */

/**
 * @package     Nexista
 * @subpackage  Actions
 * @author      Albert Lash
 */
 
/**
 * This action decrypts data with a key
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Decrypt_KeyAction extends Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'pw' => '', //required - pw, used as key to decrypt encryption key
        'key' => '' //required - key from database to decrypt
        );


    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {   
        
		$my_pw = Flow::find($this->params['pw']);        
		$my_key = Flow::find($this->params['key']);
        if($my_key->length === 1)
        {            
			// set IV
			if(function_exists('mcrypt_get_iv_size')) { 
				$iv_size = mcrypt_get_iv_size(MCRYPT_XTEA, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
			} else { 
				$iv="";
			}
			
			// DECRYPT KEY
			$my_key=base64_decode($my_key->item(0)->nodeValue);
			if(function_exists('mcrypt_get_iv_size')) { 
			$key = mcrypt_decrypt(MCRYPT_3DES, $my_pw->item(0)->nodeValue, $my_key, MCRYPT_MODE_ECB, $iv);
			Flow::add("key", $key);
			}
			
			
			
            return true;
        }
         
        return false;
            

    }
} //end class

?>