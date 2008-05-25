<?php
/*
 * -File        insert.builder.php
 * -License     LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright   Nexista
 * -Author      joshua savage
 * -Author      albert lash
 */

/**
 * @package     Nexista
 * @subpackage  Builders
 * @author      Joshua Savage 
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
        $application = Nexista_Foundry::singleton();

        //get all blocks
        $x = new DOMXPath($application->sitemapDocument);
        $blocks = $x->query('//map:block');
      
        //find the one with correct name
        $found = false;
        foreach($blocks as $block)
        {
            $name = $block->getAttribute('name');

            if($name == $blockName)
            {
                /*insert block section in here. Easiest is to insert with map:block tag and remove the name attribute to prevent parsing of this tag for later inserts*/
                $clone = $block->cloneNode(1);
                $new = $application->sitemapDocument->importNode($clone,1);
                $this->action->parentNode->insertBefore($new,$this->action->nextSibling);
                $found = true;
              
                break;
            }
        }

        //nothing found - let's send a warning and return gracefully
        if(!$found)
        {
            Nexista_Error::init('A matching map:block of name: '.$blockName.' was not found in sitemap', NX_ERROR_FATAL);

            //TODO need better exit?
            return null;
        }
         
        return null;

    }
}

?>