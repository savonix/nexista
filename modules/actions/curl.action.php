<?php
/**
 * -File        Curl.Action.php
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
 * This action calls a URL with the same session as the current client,
 * including some variables and the flow variable to return the result.
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_curlAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var     array
     */

     protected  $params = array(
        'url' => '', // where to make the curl request
        'params' => '', // what parameters to make the request with
        'target_node' => '' //where to store the response data
        );


    /**
     * Applies action
     *
     * @return  boolean     success
     */
     
    protected function main()
    {

        // For now, limit calls to the same domain and protocol.
        $mydomain = $_SERVER['SERVER_NAME'];
        if(isset($_SERVER['HTTPS'])) {
            $protocol="https://";
        } else {
            $protocol="http://";
        }
        $url = $this->params['url'];
        $the_params = $this->params['params'];
        $my_params = Nexista_Flow::getByPath($the_params,"ASSOC");
        $target_node = $this->params['target_node'];
       //print_r($my_params);
        
        if(is_array($my_params))  {
            foreach ($my_params as $key => $value) {
                if(is_array($value))  {
                    foreach ($value as $my_key => $my_value) { 
                        //Only adds the query piece if its not already there. 
                        //tried array_unique earlier but it didn't work with 
                        //array of arrays.
                        $query_piece="&".urlencode($my_key)."[]=".urlencode($my_value);
                        if(strpos($query_string,$query_piece)===false) { 
                                $query_string.=$query_piece;
                        }
                    }

                } else {
                    $query_string="&".urlencode($key)."=".urlencode($value);
                }
            }
        }
        //echo $query_string;
        $url .= $query_string;
        if(!strpos($url,"www.")) { 
            $url = $protocol.$mydomain.$url;
        } else {
        }
        // Quick hack to allow overriding above logic with complete,
        // off-domain url
        if(strstr($this->params['url'],'http://')) {
            $url = $this->params['url'];
        }
        if(function_exists(curl_init)) {
            session_write_close();
            $mysession=session_name().'='.session_id();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_COOKIE, $mysession);
            curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $xml = curl_exec ($ch);

            if (curl_errno($ch)) {
               print curl_error($ch);
               exit;
            } else {
               curl_close($ch);
            }
        } else { 
          $xml = "<groups>Curl PHP extension is not available.</groups>";
        }

        Nexista_Flow::add($target_node,$xml);
    }

} //end class

?>