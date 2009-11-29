<?php
/**
 * -File        Foundry.php
 * -Copyright   Savonix Corporation
 * -Author      Albert Lash
 * -Author      joshua savage
 *
 * PHP version 5
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */

/**
* Load required runtime files
*/
require_once NX_PATH_CORE . 'error.php';
require_once NX_PATH_CORE . 'pathbuilder.php';
require_once NX_PATH_CORE . 'debug.php';
require_once NX_PATH_CORE . 'config.php';
require_once NX_PATH_CORE . 'builder.php';
require_once NX_PATH_CORE . 'singleton.php';

define('NX_BUILDER_LINEBREAK', "\n");

Nexista_Error::addObserver('display', 'Nexista_builderError');

/**
 * Error...
 *
 * @param object $e error object
 *
 * @return null
 */

function Nexista_builderError($e)
{
    if ($e->getCode() == NX_ERROR_FATAL ||
        $e->getCode() == NX_ERROR_WARNING
        ) {
            $e->toText();
    }
}



/**
 * This class is responsible for building a site/application
 * based on desired sitemap and configuration settings.
 *
 * The build process creates a compiled php file from the sitemap
 * definition as well as the necessary file to handle the logic of
 * presenting these files based on request.
 *
 * A typical build would output:
 * - A loader file such as index.php which is used as the 'entrance' file for
 * the application.
 * - A switchbox file which is responsible for returning the proper data based
 * on request
 * - A number of 'gate' php files, one for each section or 'page' of the sitemap
 * which
 * are responsible for handling the per request logic and will load the
 * necessary module files such as query definitions, php scripts, xsl stylesheets
 * - A configuration file based on our preferences.
 *
 * To build an application, you will need a script to call the Foundry process.
 * Here is some sample code:
 * <code>
 * <?php
 * //load the application builder class
 * require_once('/home/lotus/nexista/kernel/foundry.php');
 *
 * //instanciate and initialize it with our desired registry file
 * $foundry = Nexista_Foundry::singleton('Nexista_Foundry');
 *
 * //load the master config, the user override config and process for 'live' mode
 * $foundry->configure('./master.xml','./user.xml', 'live');
 *
 * //build loader (i.e. index.php)
 * $foundry->buildLoader();
 * //compile the application
 * $foundry->buildGates(); 
 * $foundry->buildSitemap();
 * ?></code>
 *
 * @category  Nexista
 * @package   Nexista
 * @author    Albert Lash <albert.lash@savonix.com>
 * @copyright 2009 Savonix Corporation
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL
 * @link      http://www.nexista.org/
 */
class Nexista_Foundry extends Nexista_Singleton
{


    /**
     * Sitemap root node object
     *
     * @var object
     */

     public $sitemapDocument;


    /**
     * Array to keep track of what tags are handled by what builder
     *
     * @var array
     */

    private $_builderTags = array();


    /**
     * Holds the gate info used to compile the cached sitemap file
     *
     * @var array
     */

    private $_sitemapData;

    /**
     * Print debug info when building
     *
     * @var boolean
     */

    public $debug = false;


    /**
     * Read and writes the application config data
     *
     * This method loads the config data that holds the applciation
     * parameters such as paths, location of sitemap,
     * session preferences, db connections, etc...
     * Some of this data will be written directly in the gate files
     * during the compile process. It takes the config xml and ouputs
     * it as combnined xml file for runtime.
     *
     * @param string $master          master config file
     * @param string $local           optional local override configuration file
     * @param string $mode            optional mode
     * @param string $config_filename optional environment profile
     *
     * @return null
     */

    public function configure($master,
        $local = null, $mode = null, $config_filename = 'config.xml')
    {

        $config = Nexista_Config::singleton('Nexista_Config');
        $config->setMaster($master);
        if(is_file($local)) {
            $config->setLocal($local);
        }
        $config->setMode($mode);
        $config->load();
        if($config->writeConfig($config, $config_filename)) {
            return true;
        } else {
            return false;
        }

        //init debug
        $configs = Nexista_Config::getSection('runtime');
        if ($configs['debug']) {
            $GLOBALS['debugTrack'] = true;
        }
    }


    /**
     * Returns the path to the sitemap
     * Useful for checking the mod time for required rebuilds
     *
     * @return string path to sitemap
     */

    public function getSitemapPath()
    {

        return Nexista_Config::get('./build/sitemap');

    }


    /**
     * Returns the path to the compile directory
     *
     * @return string path to sitemap
     */

    public function getCompilePath()
    {

        return Nexista_Config::get('./path/compile');

    }

    /**
     * Builds the loader file (i.e. index.php)
     *
     * This method creates a loader file based on config settings.
     * This file is used as the 'entrance' file for the site, loading the
     * sitemap and appropriate gate file.
     *
     * @return boolean success of writing loader file
     */

    public function buildLoader()
    {

        $code[] = '<?php';
        $code[] = '/*';
        $code[] = ' * Domain: '.$_SERVER['SERVER_NAME'];
        $code[] = ' * Built: '.date("D M j G:i:s T Y");
        $code[] = ' */';

        //$key = $_ENV['NEXISTA_MODE'];
        $modes = Nexista_Config::getSection('modes');
        foreach ($modes as $key => $value) {
            /* Note: single quotes are faster in PHP */
            Nexista_Config::setMode($key);
            $path = Nexista_Config::getSection('path');
            $nxid = Nexista_Config::get('./build/query');
            $hand = $path['base'] . 'modules/handlers/';
            $acts = $path['base'] . 'modules/actions/';
            $vals = $path['base'] . 'modules/validators/';

            $code[] = 'if (!isset($_ENV[\'NEXISTA_MODE\'])) {';
            $code[] = '    $_ENV[\'NEXISTA_MODE\'] = \''.$key.'\';';
            $code[] = '}';
            $code[] = 'if ($_ENV[\'NEXISTA_MODE\']==\''.$key.'\') { ';
            $code[] = 'define(\'NX_PATH_HANDLERS\', \''.$hand.'\');';
            $code[] = 'define(\'NX_PATH_ACTIONS\', \''.$acts.'\');';
            $code[] = 'define(\'NX_PATH_VALIDATORS\', \''.$vals.'\');';
            $code[] = 'define(\'NX_PATH_COMPILE\', \''.$path['compile'].'\');';
            $code[] = 'define(\'NX_PATH_CACHE\', \''.$path['cache'].'\');';
            $code[] = 'define(\'NX_PATH_TMP\', \''.$path['tmp'].'\');';
            $code[] = 'define(\'NX_PATH_APPS\', \''.$path['applications'].'\');';
            $code[] = 'define(\'NX_PATH_PLUGINS\', \''.$path['plugins'].'\');';
            $code[] = 'require_once(NX_PATH_CORE.\'init.php\');';
            $code[] = 'Nexista_Config::setMode(\''.$key.'\');';
            $code[] = 'define(\'NX_ID\', \''.$nxid.'\');';

            $code[] = '$init = new Nexista_Init();';

            $extensions = Nexista_Config::getSection('extensions');
            if (is_array($extensions)) {
                foreach ($extensions as $extension => $value) {
                    $thisExtension = Nexista_Config::getSection($extension,
                        false, '/extensions/');
                    if ($thisExtension['placement'] == 'prepend') {
                        $code[] =
                        '$init->loadPrepend(\''.$thisExtension['source'].'\');';
                    }
                }
            }

            $code[] = '$init->start();';

            $extensions = Nexista_Config::getSection('extensions');
            if (is_array($extensions)) {
                foreach ($extensions as $extension => $value) {
                    $thisExtension = Nexista_Config::getSection($extension,
                        false, '/extensions/');
                    if ($thisExtension['placement'] == 'predisplay') {
                        $code[] = 
                        '$init->loadPrepend(\''.$thisExtension['source'].'\');';
                    }
                }
            }
            $code[] = '$init->display();';
            $code[] = '$init->stop();';
            $code[] = '}';
            $code[] = '';

        }
        $code[] = '?>';

        foreach ($modes as $key => $value) {
            Nexista_Config::setMode($key);
            $mydir = Nexista_Config::get('./build/loader');
            return file_put_contents($mydir,
                implode(NX_BUILDER_LINEBREAK, $code));
        }
    }

    /**
     * Loads the sitemap
     *
     * @return null
     */

    private function _loadSitemap()
    {
        if (isset($_ENV['NEXISTA_MODE'])) {
            Nexista_Config::setMode($_ENV['NEXISTA_MODE']);
        }

        //read sitemap as xml
        $this->sitemapDocument = new DOMDocument("1.0", "UTF-8");

        $my_sitemap = Nexista_Config::get('./build/sitemap');

        $this->sitemapDocument->load($my_sitemap);
        $gate_items = $this->sitemapDocument->getElementsByTagName('*');
        foreach ($gate_items as $gate_i) {
            if ( $gate_i->hasAttribute("src") ) {
                $my_src = $gate_i->getAttribute("src");
                // Do not alter absolute or starting with inline flow paths
                if($my_src[0]!="/" && $my_src[0]!="{") {
                    $gate_i->removeAttribute("src");
                    $gate_i->setAttribute("src", dirname($my_sitemap)."/".$my_src);
                }
            }
        }

        //process extensions sitemaps
        $extensions = Nexista_Config::getSection('extensions');
        if (is_array($extensions)) {
            foreach ($extensions as $extension => $value) {
                $thisExtension = Nexista_Config::getSection($extension,
                    false, '/extensions/');
                if ( $ext_sitemap = $thisExtension['sitemap'] ) {
                    if ( is_file($ext_sitemap) ) {
                        $zdoc = new DOMDocument();
                        $zdoc->load($ext_sitemap);
                        $gate_items = $zdoc->getElementsByTagName('*');
                        foreach ($gate_items as $gate_item) {
                            if ( $gate_item->hasAttribute("src") ) {
                                $my_src = $gate_item->getAttribute("src");
                                $gate_item->removeAttribute("src");
                                $gate_item->setAttribute("src",
                                    dirname($ext_sitemap) . "/" . $my_src);
                            }
                        }
                        // Only import gates
                        $z = new DOMXPath($zdoc);
                        
                        $zimported_gates = $z->query('//map:gate');
                        foreach ($zimported_gates as $zimport) {
                            $a = $this->sitemapDocument->importNode($zimport, 1);
                            $this->sitemapDocument->documentElement->appendChild($a);
                        }
                    }
                }
            }
        }
    }


    /**
     * Parses the sitemap, calling build process for each gate
     *
     * @return null
     */

    public function buildGates()
    {
        //load sitemap
        $this->_loadSitemap();

        //load builder classes
        $builderPath = Nexista_Config::get('./path/base').'modules/builders/';

        $files = scandir($builderPath);
        //make instances for each for later gate building
        foreach ($files as $file) {
            //the # is for CVS backups which get in the way, as well as backups
            if (strpos($file, '.builder.php') AND
                !strpos($file, '#') AND
                !strpos($file, '_') AND
                !strpos($file, '~')
                ) {

                //load class
                include_once $builderPath.$file;

                //store class
                $tag   = str_replace('.builder.php', '', $file);
                $class = 'Nexista_'.ucfirst($tag).'Builder';
                $obj   =& new $class;

                $this->builderTags[$tag] =& $obj;
            }
        }



        //go through each gate
        $x = new DOMXPath($this->sitemapDocument);

        $gates = $x->query('//map:gate');
        $count = 0;

        foreach ($gates as $gate) {
            //build gate
            $this->_writeGate($this->_parseGate($gate), $count);

            //add gate info for sitemap
            $this->_buildConditions($gate, 'gate-'.$count.'.php');
            $count++;
        }
        if ($this->debug) {
            echo '<script type="text/javascript">var done_building = "true";</script>';
        }

    }


    /**
     * Compiles the final sitemap file
     *
     * @param string &$gate    xml array path to the gate info
     * @param string $filename filename of gate for the sitemap file
     *
     * @return null
     */

    private function _buildConditions(&$gate, $filename)
    {
        if (!$gate->hasAttribute('name')) {
            Nexista_Error::init('No name for gate', NX_ERROR_FATAL);
        }

        //http method
        $http_method = 'get';
        if ($gate->hasAttribute('http_method')) {
            $http_method = $gate->getAttribute('http_method');
        }

        //cache time
        $cache = -1;
        if ($gate->hasAttribute('cache')) {
            $cache = $gate->getAttribute('cache');
        }

        //cache_control
        $cache_control = -1;
        if ($gate->hasAttribute('cache_control')) {
            $cache_control = $gate->getAttribute('cache_control');
        }

        //content_type
        $content_type = -1;
        if ($gate->hasAttribute('content_type')) {
            $content_type = $gate->getAttribute('content_type');
        }

        //role required
        $role = -1;
        if ($gate->hasAttribute('role')) {
            $role = $gate->getAttribute('role');
        }

        if (preg_match('~^regex:(.*)$~', $gate->getAttribute('name'), $m)) {
            $match = 'regex';
            $name  = $m[1];
        } else {
            $match = 'exact';
            $name  = $gate->getAttribute('name');
        }

        if ($this->debug) {
            echo "<a href='?nid=".$name."'>".$name."</a>...<br/>\n";
        }

        $this->sitemap[$match][$http_method.':'.$name] =
            array(
                'uri' => $filename,
                'role' => $role,
                'http_method' => $http_method,
                'cache' => $cache,
                'cache_control' => $cache_control,
                'content_type' => $content_type
            );
    }


    /**
     * Compiles the final sitemap file
     *
     * This method takes $this->sitemap array which
     * contains all the gate info and builds an if/else
     * condition map out of it. This file will be used
     * as the sitemap for the site
     *
     * @return null
     */

    public function buildSitemap()
    {

        //build top of file (reqs, etc)
        $code[] = '<?php';
        $code[] = '//Built: '.date("D M j G:i:s T Y");
        $my_nid = Nexista_Config::get('./build/query');
        $code[] = '$_ID_ = Nexista_Path::get("{//_get/'.$my_nid.'}");';

        $default_gate = Nexista_Config::get('./build/default_gate');
        if (!empty($default_gate)) {
            $code[] = 'if (empty($_ID_)) { $_ID_ = "'.$default_gate.'"; }';
        }

        foreach ($this->sitemap as $type => $elements) {
            $code[] = '$gates'.ucfirst($type).' = array(';

            foreach ($elements as $name => $info) {
                $this_gate = "'".$name."'=>array('uri'=>'".$info['uri']."'";


                // Server cache's need to include auth if there is a role .
                if ($info['cache'] !== -1 && $info['role'] !== -1) {
                    $this_gate .= ",
                        'cache'=>".$info['cache'].",
                        'role'=>'".$info['role']."'";

                } elseif ($info['cache'] !== -1 && $info['role'] === -1) {
                    $this_gate .= ",'cache'=>".$info['cache'];

                } elseif ($info['role'] !== -1 && $info['cache'] === -1 ) {
                    $this_gate .= ",'role'=>'".$info['role']."'";
                }

                /* Headers */
                // Cache-Control
                if ($info['cache_control'] !== -1) {
                    $this_gate .= ",'cache_control'=>'".$info['cache_control']."'";
                }

                // Content type
                if ($info['content_type'] !== -1) {
                    $this_gate .= ",
                        'content_type'=>'".$info['content_type']."'";
                }
                $this_gate .= "),";

                $code[] = $this_gate;
            }
            $code[] = ');';
        }

        //setup 404 handling
        $missing = Nexista_Config::get('./build/missing');
        if (!empty($missing) && isset($this->sitemap['exact'][$missing])) {
            $code[] = '$gateMissing = array(';
            foreach ($elements as $name => $info) {
                if ($name==$missing) {
                    $this_gate = "'uri'=>'".$info['uri']."'";
                    // Server cache's need to include auth if there is a role
                    if ($info['cache'] !== -1 && $info['role'] !== -1) {
                        $this_gate .= ",
                            'cache'=>".$info['cache'].",
                            'role'=>'".$info['role']."'";

                    } elseif ($info['cache'] !== -1 && $info['role'] === -1) {
                        $this_gate .= ",'cache'=>".$info['cache'];

                    } elseif ($info['role'] !== -1 && $info['cache'] === -1) {
                        $this_gate .= ",'role'=>'".$info['role']."'";
                    }
                    /* Headers */
                    // Cache-Control header
                    if ($info['cache_control'] !== -1) {
                        $this_gate .= ",
                            'cache_control'=>'".$info['cache_control']."'";
                    }
                    // Content type
                    if ($info['content_type'] !== -1) {
                        $this_gate .= ",
                            'content_type'=>'".$info['content_type']."'";
                    }
                    $code[] = $this_gate;
                }
            }
            $code[] = ');';
        }
        else
            $code[] = '$gateMissing = null;';

        $code[] = '?>';

        $data = implode(NX_BUILDER_LINEBREAK, $code);

        //save file
        $tmp = fopen(Nexista_Config::get('./path/compile').'sitemap.php', "w+");
        if (flock($tmp, LOCK_EX)) {
            fwrite($tmp, $data);
            flock($tmp, LOCK_UN);
        }
        fclose($tmp);
    }


    /**
     * Build an individual gate file
     *
     * @param object &$gate reference to gate object
     *
     * @return string content of gate to write
     */

    private function _parseGate(&$gate)
    {
        //init array for required file
        $required = array();

        $code = '';

        //foreach action
        $this->_parseGateCallback($gate, $code, $required);

        //get prepend tags
        $res = $this->sitemapDocument->getElementsByTagName('prepend');
        
        $prependCode = '';
        if ($res->length) {
            $prependGate = $res->item(0);
            $this->_parseGateCallback($prependGate, $prependCode, $required);
        }

        //add header and required files
        $content = $this->_addGateHeader($gate, $required);

        //add prepend and main code
        $content .= $prependCode.$code;
        $content .= $this->_addGateFooter($gate);

        return $content;

    }


    /**
     * Callback file to build an individual gate file
     *
     * @param object &$tag      reference to current tag being processed
     * @param string &$code     reference to current code content for gate
     * @param array  &$required reference to required include files for this tag
     *
     * @return boolean success
     */

    private function &_parseGateCallback(&$tag, &$code, &$required)
    {
        foreach ( $tag->childNodes as $action) {
            if (get_class($action) != 'DOMElement')
                continue;

            $module = str_replace('map:', '', $action->nodeName);

            if (in_array($module, array_keys($this->builderTags))) {
                $obj =& $this->builderTags[$module];

                //add required files
                $required = array_merge($required, $obj->getRequired());

                //pass current object node if needed
                $obj->action =& $action;

                //add debug code?
                $non_debug_modules =
                    array('true', 'false', 'case', 'default');

                if ($GLOBALS['debugTrack'] &&
                    !in_array($module, $non_debug_modules)) {
                    $code .= $this->_addGateDebugStart($module);
                }

                //get start of code
                $text = $obj->getCodeStart();

                if (!empty($text))
                    $code .= $text.NX_BUILDER_LINEBREAK;

                //get nested tag code
                if (!$this->_parseGateCallback($action, $code, $required))
                    return false;

                //get end code
                $text = $obj->getCodeEnd();

                if (!empty($text))
                    $code .= $text.NX_BUILDER_LINEBREAK;

                //reset builder values such as attributes
                $obj->reset();

                //add debug code?
                if ($GLOBALS['debugTrack'] &&
                    !in_array($module, $non_debug_modules)) {
                    $code .= $this->_addGateDebugStop($module);
                }
            } else {
                //Nexista_Error::init("No $module builder module found!",
                //    NX_ERROR_WARNING);
            }
        }
        return true;
    }


    /**
     * Compiles individual gate files
     *
     * @param string &$gatedata the cdata content for this gate
     * @param string &$gatenum  the number (in order) of this gate
     *
     * @return null
     */

    private function _writeGate(&$gatedata, &$gatenum)
    {

        //write gate file
        $compile_path = Nexista_Config::get('./path/compile');
        if (!is_dir($compile_path)) {
            // TODO - Error handling
            mkdir($compile_path, 0775, true);
        }
        
        $gatefile = fopen($compile_path. 'gate-'.$gatenum.".php", "w+");
        if (flock($gatefile, LOCK_EX)) {
            fwrite($gatefile, $gatedata);
            flock($gatefile, LOCK_UN);
        }
        fclose($gatefile);
    }


    /**
     * Adds gate debug registration
     *
     * @param string $mod prepend code
     *
     * @return string
     */

    private function _addGateDebugStart($mod)
    {
        $code[] = "Nexista_Debug::register('in','".$mod."');";
        return implode(NX_BUILDER_LINEBREAK, $code).NX_BUILDER_LINEBREAK;
    }


    /**
     * Returns gate prepend code
     *
     * @param string $mod prepend code
     *
     * @return string
     */

    private function _addGateDebugStop($mod)
    {
        $code[] = "Nexista_Debug::register('out','".$mod."');";
        return implode(NX_BUILDER_LINEBREAK, $code).NX_BUILDER_LINEBREAK;
    }

    /**
     * Returns gate prepend code
     *
     * @param object &$obj prepend code
     *
     * @return string
     */

    private function _addGateFooter(&$obj)
    {
        $code[] = '?>';
        return implode(NX_BUILDER_LINEBREAK, $code);
    }


    /**
     * Returns gate prepend code
     *
     * @param object &$obj prepend code
     * @param array  $req  array of required resources
     * 
     * @return string code
     */

    private function _addGateHeader(&$obj, $req = array())
    {
        $code[] = '<?php';
        $code[] = '/*';
        $code[] = ' * Gate Name:     '.$obj->getAttribute('name');

        $matchType = ($obj->getAttribute('match')== 'regex') ? 'regex':'normal';

        $code[] = ' * Match Type:    '.$matchType;
        $code[] = ' * Build Time:    '.date("D M j G:i:s T Y");
        $code[] = ' */';

        $req = array_unique($req);
        foreach ($req as $r) {
            $code[] = "require_once('".$r."');";
        }

        $code[] = '$flow = Nexista_Flow::singleton("Nexista_Flow");';
        $code[] = '$output  = null;';

        return implode(NX_BUILDER_LINEBREAK, $code).NX_BUILDER_LINEBREAK;
    }


} //end class
?>
