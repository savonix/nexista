<?php
/**
 * -File        Insert.Builder.php
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
 * This class handles the tag by the same name in the sitemap building process
 *
 * @package     Nexista
 * @subpackage  Builders
 */

class Nexista_InsertBuilder extends Nexista_Builder
{

    /**
     * Returns code for this tag.
     *
     * @return   string Final code to insert in gate
     * @see      Nexista_Builder::getCode()
     */

    public function getCodeStart()
    {
        //block name to insert
        $blockName = $this->action->getAttribute('name');

        //get instance of Application
        $application = Nexista_Foundry::singleton('Nexista_Foundry');

        //get all blocks
        $x = new DOMXPath($application->sitemapDocument);
        $blocks = $x->query('//map:block');

        //find the one with correct name
        $found = false;
        foreach($blocks as $block)
        {
            $name = $block->getAttribute('name');

            if($name == $blockName) {
                /*insert block section in here. Easiest is to insert with
                map:block tag and remove the name attribute to prevent parsing
                of this tag for later inserts*/
                $clone = $block->cloneNode(1);
                $new = $application->sitemapDocument->importNode($clone,1);
                $this->action->parentNode->insertBefore($new,
                    $this->action->nextSibling);
                $found = true;

                break;
            }
        }

        //nothing found - let's send a warning and return gracefully
        if(!$found) {
            Nexista_Error::init('A matching map:block of name:
                '.$blockName.' was not found in sitemap', NX_ERROR_WARNING);

            return null;
        }

        return null;

    }
}

?>