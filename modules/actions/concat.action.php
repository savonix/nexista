<?php
/**
 * -File        Concat.Action.php
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
 * This action concatenates params
 *
 * @package     Nexista
 * @subpackage  Actions
 */

class Nexista_ConcatAction extends Nexista_Action
{

    /**
     * Function parameter array
     *
     * @var array
     */

    protected  $params = array(
        'var1' => '', //required - first part of concatenation
        'var2' => '', //required - second part of concatenation
        'var3' => '', //required - existing xpath node or new node name
        );

    /**
     * Applies action
     *
     * @return  boolean success
     */

    protected  function main()
    {
        // if using a single node, use {}'s in sitemap
        // if referencing a nodeset, don't use {}'s
        $xpath1 = $this->params['var1'];
        $res1   = Nexista_Flow::find($xpath1);
        $xpath2 = $this->params['var2'];
        $res2   = Nexista_Flow::find($xpath2);
        $xpath3 = $this->params['var3'];
		$res3   = Nexista_Flow::find($xpath3);
        $i      = 0;

        if ($res3->item(0)) {
            if ($res3->length === 1) {
                // there is a node, replace it, since its only one, res1 must
                // only be one as well
                echo Nexista_Path::parseInlineFlow($this->params['var2']);
                $res3->item(0)->nodeValue = Nexista_Path::parseInlineFlow($this->params['var1']) . Nexista_Path::parseInlineFlow($this->params['var2']);
                return true;
            } elseif ($res3->length > 1) {
                // res3 is an array, so res3 is too
                foreach($res1 as $str) {
                    if($res2->length > 1 || $res2->length === 1) {
                        $res3->item($i)->nodeValue =  $res1->item($i)->nodeValue . $res2->item($i)->nodeValue;
                    } else {
                        $res3->item($i)->nodeValue =  $str->nodeValue . Nexista_Path::parseInlineFlow($this->params['var2']);
                    }
                    $i++;
                }
                return true;
            } else {
                // create a new node
                $new_node = $this->params['var3'];
                Nexista_Flow::add($new_node, $res1 . $res2);
                return true;
            }
        }

        return false;

    }
} //end class

?>