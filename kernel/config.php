<?php
/**
 * -File        Config.php
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
 * The configuration system of Nexista is XML based and is designed to allow for 
 * multiple modes (i.e live, development, etc...).
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@gmail.com>
 * @copyright 0000 Nexista
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */


require_once NX_PATH_CORE . 'singleton.php';

class Nexista_Config extends Nexista_Singleton
{

    /**
     * Master config file (i.e. master.xml)
     *
     * @var string
     */

    private $_masterConfig;


    /**
     * Local config file (i.e. local.xml)
     *
     * @var string
     */

    private $_localConfig = null;

    /**
     * Config mode/environment
     *
     * @var string
     */

    static public $mode = null;


    /**
     * SimpleXML root object of merged master/local
     *
     * @var object
     */

    static public $xml;


    /**
     * Sets the master application config data
     * 
     * This method loads the config data that holds the applciation
     * parameters such as paths, location of sitemap, 
     * session preferences, db connections, etc...
     * Some of this data will be written directly in the gate files
     * during the compile process.
     *
     * @param string $file path to xml config file
     *
     * @return null
     */

    public function setMaster($file)
    {
        if (!file_exists($file)) {
            Nexista_Error::init('Cannot find master config file: '.$file,
                NX_ERROR_FATAL);
        }
        $this->masterConfig = $file;
    }


    /**
     * Sets the local override application config data
     *
     * This method accepts a local config file that can be used to:
     * 1. override master config settings
     * 2. create custom settings that can later be accessed in the standard manner
     *
     * @param string $file path to xml config file
     *
     * @return null
     */
     
    public function setLocal($file)
    {
        if ($file) {
            if (!file_exists($file)) {
                Nexista_Error::init('Cannot find local config file: '.$file,
                    NX_ERROR_FATAL);
            }
            $this->localConfig = $file;
        }
    }

    /**
     * Sets the configuration mode
     *
     * A mode is set in the config files for each entry in case of multiple
     * entries of the same type that may be used in different environment
     * such as live, dev, debug mode.
     * This mode, which is optional but if given it will be used by the config 
     * process to determine which entry to retrieve.
     * 
     * @param string $mode mode
     *
     * @return null
     */
    static public function setMode($mode)
    {

        self::$mode = $mode;

    }

    /**
     * Retrieves the current mode
     *
     * @return string mode
     */
 
    static public function getMode()
    {

        return  self::$mode;

    }


    /**
     * Reads and parses all config data
     *
     * Reads the master and optional local config file into a common
     * simpleXML object
     *
     * @return null
     */

    public function load()
    {
        $directives  = '<!ENTITY includepath "'.NX_PATH_BASE.'">';
        $directives .= '<!ENTITY server_name "'.$_SERVER['SERVER_NAME'].'">';
        $directives .= '<!ENTITY project_root "'.PROJECT_ROOT.'">';
        $directives .= '<!ENTITY project_name "'.PROJECT_NAME.'">';
        $directives .= '<!ENTITY app_name "'.APP_NAME.'">';

        //if a local config is passed we merge the two in a valid xml string
        $localfile = file_get_contents($this->localConfig);
        if (!empty($localfile)) {
            preg_match('~<config>(.*)</config>~ms', $localfile, $u);
            preg_match('~<config>(.*)</config>~ms', 
                file_get_contents($this->masterConfig), $g);

            $xtring  = '<?xml version="1.0"?>';
            $xtring .= '<!DOCTYPE config [';
            $xtring .= $directives;
            $xtring .= ']><config>'.$u[1].$g[1].'</config>';

            self::$xml = simplexml_load_string($xtring);
        } else {
            preg_match('~<config>(.*)</config>~ms', 
                file_get_contents($this->masterConfig), $n);

            $xtring  = '<?xml version="1.0"?>';
            $xtring .= '<!DOCTYPE config [';
            $xtring .= $directives;
            $xtring .= ']><config>'.$n[1].'</config>';

            self::$xml = simplexml_load_string($xtring);
        }

    }
    /**
     * Reads the master
     *
     * @return null
     */

    public function loadMasterConfig()
    {

        self::$xml = simplexml_load_file($this->masterConfig);

    }

    /**
     * Return master config simple xml object
     *
     * @return null
     */

    public function returnMasterConfig()
    {

        $this->xml = simplexml_load_file($this->masterConfig);

    }


    /**
     * Writes a combined config file for runtime
     *
     * The master config and optional local config are output as a combined xml
     * file that is used by the runtime system
     *
     * @param string &$config         config
     * @param string $config_filename path to xml config file
     *
     * @return null
     */

    static public function writeConfig(&$config,$config_filename)
    {
        $canonical_filename =
            Nexista_Config::get('./path/compile').$config_filename;

        $config_compile_error =
        "Can't open $canonical_filename.
        Check permissions of parent directories,
        or simply refresh to try and rebuild it.
        chmod 0777 $canonical_filename ?";

        $tdir = dirname($canonical_filename);

        if (! is_dir($tdir)) mkdir($tdir, 0777, true);

        if ($tmp = fopen($canonical_filename, "w+")) {
            if (flock($tmp, LOCK_EX)) {
                fwrite($tmp, self::$xml->asXML());
                flock($tmp, LOCK_UN);
            } else {
                Nexista_Error::Init($config_compile_error, NX_ERROR_FATAL);
            }
            fclose($tmp);
        }
    }


    /**
     * Retrieves a config value
     *
     * Retrieves a value for a config variable. If a mode is set
     * it will attempt to get the value for this variable in the preferred mode.
     * If nothing is found, it will then attempt to retrieve the default value in
     * local config file and will finally look for the default master value.
     * Note that this method makes use of xpath and adds on the mode criteria
     *
     * @param string $name variable path
     *
     * @return mixed value or null if not found
     */

    static public function get($name)
    {
         $result = 0;

         //is this a parent node?
        if (!is_null(self::$mode)) {
            $result =
                self::$xml->xpath($name."[@mode='".self::$mode."'][not(*/node())]");
        }

        //no mode given or none found with a given mode
        if (!$result) {
            $result = self::$xml->xpath($name.'[not(@mode)][not(*/node())]');
        }

        if ($result) {
            return (string)$result[0];
        }
        else
            return null;
    }


    /**
     * Retrieves a config section
     *
     * Retrieves an array of values for a config section. If a mode is set
     * it will attempt to get the value for these variable in the preferred mode.
     * If nothing is found, it will then attempt to retrieve the default value in
     * local config file and will finally look for the default master value.
     *
     * @param string $name       section path
     * @param string $id         section id for multiple sections with same name
     * @param string $subsection sub
     *
     * @return array empty if nothing found
     */

    static public function getSection($name, $id = false, $subsection = '')
    {
        if ($id) {
            $res =
                self::$xml->xpath('//config/'.$subsection.$name.'[@id="'.$id.'"]');
            $obj = $res[0];
        } else {
            $res = self::$xml->xpath('//config/'.$subsection.$name.'[not(@id)]');
            $obj = $res[0];
        }

        $result = array();
        if (is_object($obj)) {
            foreach ($obj->children() as $k=> $v) {
                if (is_null(self::$mode) AND 
                    (!$v['mode'] OR (string)$v['mode'] == $mode )) {

                    $result[$k] = (string)$v;

                } else { //if child has mode match we use it

                    if ((string)$v['mode'] === self::$mode) {
                        $result[$k] = (string)$v;

                    } elseif (!isset($result[$k])) {
                        //get default value unless a moded one is already in
                        $result[$k] = (string)$v;
                    }
                }
            }
        } else {
            Nexista_Error::init('The "'.$name.'" section with id '.$id.' does
                not exist in the configuration', NX_ERROR_NOTICE);
            return false;
        }
        return $result;
    }



}

?>