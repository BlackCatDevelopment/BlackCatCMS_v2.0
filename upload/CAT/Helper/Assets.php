<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2017 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

if(!class_exists('CAT_Helper_Assets'))
{
	class CAT_Helper_Assets extends CAT_Object
	{
        // set debug level
        protected static $loglevel  = \Monolog\Logger::EMERGENCY;
        #protected static $loglevel  = \Monolog\Logger::DEBUG;
        protected static $instance  = NULL;
        // map type to content-type
        protected static $mime_map  = array(
            'css'   => 'text/css',
            'js'    => 'text/javascript',
            'png'   => 'image/png',
        );
        //
        protected static $includes  = array();

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * allows to add a headers.inc.php or footers.inc.php at runtime;
         * used by WYSIWYG for example to include the editor's inc files
         *
         * if $position is omitted, the method will try to get it from the
         * $file name (example: headers.inc.php -> header); defaults to
         * header on failure
         *
         * @access public
         * @param  string  $file      file path
         * @param  string  $position  header|footer (optional)
         * @return void
         **/
        public static function addInclude($file,$pos=NULL)
        {
            if(!$pos)
            {
                preg_match('~^(.*)s\.inc\.php$~i',pathinfo($file,PATHINFO_BASENAME),$m);
                $pos = ( isset($m[1]) ? $m[1] : 'header' );
            }
            if(!isset(self::$includes[$pos])) self::$includes[$pos] = array();
            self::$includes[$pos][] = CAT_Helper_Directory::sanitizePath($file);
        }   // end function addInclude()

        /**
         * collects all the assets (JS, CSS, jQuery Core & UI) for the given
         * page; $id may be a pageID or a backend area like 'backend_media'
         *
         * @access public
         * @param  string  $pos - 'header' / 'footer'
         * @param  string  $id  - pageID or 'backend_<area>'
         * @param  boolean $ignore_inc - wether to load inc files or not
         * @return AssetFactory object
         **/
        public static function getAssets($pos, $id=null, $ignore_inc=false)
        {
            list($id,$for) = self::analyzeID($id);

            self::log()->addDebug(sprintf(
                '[%s] pos [%s] id [%s] for [%s] ignore includes [%s]',
                __FUNCTION__, $pos, $id, $for, $ignore_inc
            ));

            $am = CAT_Helper_AssetFactory::getInstance($id);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Das muss anders gehen!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if($id=='backend_login')
                return $am;

            // paths to scan
            list($paths,$incpaths,$filter) = self::getPaths($id,$pos);

            // if it's a frontend page, add scan paths for modules
            if(is_numeric($id) && $id>0)
            {
                $sections = CAT_Sections::getSections($id);
                if(count($sections))
                {
                    foreach($sections as $block => $items)
                    {
                        foreach($items as $item)
                        {
                            array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$item['module'].'/css'));
                            array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$item['module'].'/js'));
                            array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$item['module']));
                        }
                    }
                }
            }
            else
            {
                $area = CAT_Backend::getArea();
                self::log()->addDebug(sprintf(
                    'looking for area specific js/css, current area: [%s]',
                    $area
                ));
                $filter .= '|'.$area;
                if($pos=='footer') $filter .= '_body';
                self::log()->addDebug(sprintf('filter: [%s]',$filter));
            }

            self::log()->addDebug('>>> scan paths');
            self::log()->addDebug('    $paths    : ' . var_export($paths,1));
            self::log()->addDebug(' $incpaths    : ' . var_export($incpaths,1));
            self::log()->addDebug('   filter     : ' . $filter);

            // -----------------------------------------------------------------
            // find default files (frontend[_body].css/js, ...)
            // -----------------------------------------------------------------
            $files    = array();
            $ext      = array('css','js');
            foreach($paths as $path)
            {
                $temp = CAT_Helper_Directory::findFiles(
                    $path,
                    array(
                        'extensions' => $ext,
                        'recurse'    => true,
                        'max_depth'  => 1,
                        'filter'     => "($filter)"
                    )
                );
                $files = array_merge($files,$temp);
            }

            self::log()->addDebug(sprintf(
                'Found [%d] files for filter [%s]',
                count($files),$filter
            ));
            if(is_array($files) && count($files)>0) {
                foreach($files as $file) {
                    self::log()->addDebug(sprintf(
                        ' --- adding file [%s] to pos [%s]',$file,$pos
                    ));
                    $am->addAsset($file,$pos); // default CSS have always media 'all'
                }
            }

            // -----------------------------------------------------------------
            // analyze headers/footers.inc
            // -----------------------------------------------------------------
            if(!$ignore_inc)
            {
                $filename = $pos.'s.inc';
                $incfiles = array();
                foreach($incpaths as $path)
                {
                    $temp = CAT_Helper_Directory::findFiles(
                        $path,
                        array(
                            'filename'   => $filename,
                            'extension'  => 'php',
                            'recurse'    => true,
                            'max_depth'  => 3
                        )
                    );
                    if(is_array($temp) && count($temp)>0)
                    {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Wenn mehrere Dateien vorhanden sind, darf pro Stammverzeichnis
//       (Modul oder Template) nur eine davon geladen werden
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        $incfiles[] = CAT_Helper_Directory::sanitizePath($temp[0]);
                        //break;
                    }
                }

                if(isset(self::$includes[$pos]) && is_array(self::$includes[$pos]))
                    $incfiles = array_merge($incfiles,self::$includes[$pos]);

                if(is_array($incfiles) && count($incfiles)>0) {
                    self::log()->addDebug(sprintf(
                        'Found [%d] include files for position [%s]',
                        count($incfiles),$pos
                    ));
                    foreach($incfiles as $file) {
                        if(file_exists($file)) {
                            try {
                                require $file;
                                $array =& ${'mod_'.$pos.'s'};
                                // CSS
                                if(array_key_exists('css',$array[$for]) && count($array[$for]['css']>0))
                                {
                                    foreach($array[$for]['css'] as $item)
                                    {
                                        if(isset($item['condition']))
                                        {
                                            foreach($item['files'] as $f)
                                            {
                                                $am->addCSS($f,(isset($item['media']) ? $item['media'] : NULL));
                                                $am->addCondition($f,$item['condition']);
                                            }
                                        } else {
                                            $am->addCSS(
                                                $item['file'],
                                                (isset($item['media']) ? $item['media'] : NULL)
                                            );
                                        }
                                    }
                                }
                                // JS
                                if(array_key_exists('js',$array[$for]) && count($array[$for]['js']>0))
                                {
                                    foreach($array[$for]['js'] as $item)
                                    {
                                        if(is_array($item))
                                        {
                                            // if it's an array there _must_ be a conditional
                                            if(!isset($item['condition'])) continue;
                                            foreach($item['files'] as $f)
                                            {
                                                $am->addJS($f,$pos);
                                                $am->addCondition($f,$item['condition']);
                                            }
                                        } else {
                                            $am->addJS($item,$pos);
                                        }
                                    }
                                }
                                // jQuery
                                if(array_key_exists('jquery',$array[$for]))
                                {
                                    if(isset($array[$for]['jquery']['core']) && $array[$for]['jquery']['core'])
                                        $am->enableJQuery();
                                    if(isset($array[$for]['jquery']['ui']) && $array[$for]['jquery']['ui'])
                                        $am->enableJQueryUI();
                                    if(isset($array[$for]['jquery']['plugins']) && $array[$for]['jquery']['plugins'])
                                    {
                                        foreach($array[$for]['jquery']['plugins'] as $item)
                                        {
                                            if(false!==($file=self::findJQueryPlugin($item)))
                                            {
                                                $am->addJS($file,$pos);
                                            }
                                        }
                                    }
                                }
                                // META
                                if(array_key_exists('meta',$array[$for]))
                                {
                                    foreach($array[$for]['meta'] as $item)
                                    {
                                        $am->addMeta($item);
                                    }
                                }
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
            }

            return $am;

        }   // end function getAssets()

        /**
         *
         * @access public
         * @return
         **/
        public static function analyzeID($id)
        {
            if(!$id)
            {
                if(CAT_Backend::isBackend())
                {
                    $id     = 'backend_'.CAT_Backend::getArea();
                    $filter = 'backend';
                    $for    = 'backend';
                } else {
                    $id = CAT_Page::getID();
                    $for = 'frontend';
                }
                return array($id,$for);
            }
            return array(
                $id,
                ( substr($id,0,7)=='backend' ? 'backend' : 'frontend' )
            );
        }   // end function analyzeID()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPaths($id,$pos)
        {
            list($id,$for) = self::analyzeID($id); // sanitize ID

            $paths    = array();
            $incpaths = array();
            $filter   = null;

            switch($for)
            {
            // -----------------------------------------------------------------
            // ----- FRONTEND --------------------------------------------------
            // -----------------------------------------------------------------
                case 'frontend':
                    $filter = 'frontend';
                    // CSS
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/css/'.CAT_Registry::get('default_template_variant')));
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/css'));
                    // JS
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/js/'.CAT_Registry::get('default_template_variant')));
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/js'));
                    // *.inc.php - fallback sorting; search will stop on first occurance
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/templates/'.CAT_Registry::get('default_template_variant')));
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/templates/default'));
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template').'/templates'));
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_template')));
                    break;
                case 'backend':
                    $filter = 'backend|theme';
                    if($pos=='footer') $filter = 'backend_body|theme_body';

                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/css/'.CAT_Registry::get('default_theme_variant')));
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/css/default'));
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/css'));

                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/templates/'.CAT_Registry::get('default_theme_variant')));
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/js/'.CAT_Registry::get('default_theme_variant')));
                    array_push($paths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/js'));

                    #$area = CAT_Backend::getArea();
                    #self::log()->addDebug(sprintf(
                    #    'looking for area specific js/css, current area: [%s]',
                    #    $area
                    #));

                    // admin tool
                    if(self::router()->match('~\/tool\/~i'))
                    {
                        $tool = CAT_Backend_Admintools::getTool();
                        foreach(
                            array_values(array(
                                CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$tool.'/css'),
                                CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$tool.'/js')
                            )) as $p
                        ) {
                            if(is_dir($p)) {
                                array_push($paths,$p);
                                array_push($incpaths,$p);
                            }
                        }
                    }

                    // fallback sorting; search will stop on first occurance
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/templates/'.CAT_Registry::get('default_theme_variant')));
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/templates'));
                    array_push($incpaths,CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme')));
                    break;
            }

            return array(array_unique($paths),array_unique($incpaths),$filter);
        }   // end function getPaths()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function renderAssets($pos, $id=null, $ignore_inc=false, $print=true)
        {
            $am = self::getAssets($pos,$id,$ignore_inc);
            $output = null;
            switch($pos)
            {
                case 'header':
                    $output = $am->renderMeta()
                            . $am->renderCSS()
                            . $am->renderJS('header');
                    break;
                case 'footer':
                    $output = $am->renderJS('footer');
                    break;
            }

            return $output;
        }   // end function renderAssets
        
        /**
         *
         * @access public
         * @return
         **/
        public static function serve($type,$files)
        {
            if(!count($files)) return false;

            if($type=='images')
            {
                self::log()->addDebug('serving image');
                foreach($files as $file)
                {
                    if(file_exists(CAT_ENGINE_PATH.'/'.$file))
                    {
                        self::log()->addDebug(sprintf(
                            'copying file [%s] to path [%s]',
                            CAT_ENGINE_PATH.'/'.$file,
                            CAT_PATH.'/assets/'.pathinfo($file,PATHINFO_BASENAME)
                        ));
                        copy(CAT_ENGINE_PATH.'/'.$file,CAT_PATH.'/assets/'.pathinfo($file,PATHINFO_BASENAME));
                        #header('Content-Type: '.self::$mime_map[strtolower(pathinfo($file,PATHINFO_EXTENSION))]);
                        echo CAT_URL.'/assets/'.pathinfo($file,PATHINFO_BASENAME);
                    }
                }
            }

            // create asset factory and pass engine path as basedir
            $factory = new \Assetic\Factory\AssetFactory(CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH));
            $fm      = new \Assetic\FilterManager();
            $factory->setFilterManager($fm);
            $factory->setDefaultOutput('assets/*');
            $factory->setProxy(CAT_Registry::get('PROXY'),CAT_Registry::get('PROXY_PORT'));

            $filters = array();
            if($type=='css')
            {
                foreach(array('CssImportFilter','CATCssRewriteFilter','MinifyCssCompressorFilter','CssCacheBustingFilter') as $filter)
                {
                    $filterclass = '\Assetic\Filter\\'.$filter;
                    $fm->set($filter,new $filterclass());
                    $filters[] = $filter;
                }
            }

            self::log()->addDebug(sprintf('type [%s], number of files [%d]', $type, count($files)).print_r($files,1));

            // add assets
            $assets  = $factory->createAsset(
                $files,
                $filters
            );

            // create the asset manager instance
            $am = new \Assetic\AssetManager();
            $am->set('assets', $assets);
            // create the writer to save the combined file
            $writer = new \Assetic\AssetWriter(CAT_Helper_Directory::sanitizePath(CAT_PATH));
            $writer->writeManagerAssets($am);
            return CAT_URL.'/'.$assets->getTargetPath();
        }   // end function serve()

        /**
         * evaluate correct item path; this resolves
         *    ./plugins/<name>.min.js
         *    ./plugins/<name>.js
         *    ./plugins/<name>/<name>.min.js
         *    ./plugins/<name>/<name>.js
         *
         * @access private
         * @param  string  $item
         * @return mixed
         **/
        private static function findJQueryPlugin($item)
        {
            $plugin_path = CAT_JQUERY_PATH.'/plugins';
            // check suffix
            if(pathinfo($item,PATHINFO_EXTENSION) != 'js')
                $item .= '.js';

            // prefer minimized
            $minitem = pathinfo($item,PATHINFO_FILENAME).'.min.js';
            $file    = CAT_Helper_Directory::sanitizePath($plugin_path.'/'.$minitem);

            // just there?
            if (!file_exists($file))
            {
                $file = CAT_Helper_Directory::sanitizePath($plugin_path.'/'.$item);
                if (!file_exists($file))
                {
                    $dir = pathinfo($item,PATHINFO_FILENAME);
                    // prefer minimized
                    $minitem = pathinfo($item,PATHINFO_FILENAME).'.min.js';
                    $file    = CAT_Helper_Directory::sanitizePath($plugin_path.'/'.$dir.'/'.$minitem);
                    if(!file_exists($file))
                    {
                        $file = CAT_Helper_Directory::sanitizePath($plugin_path.'/'.$dir.'/'.$item);
                        if(!file_exists($file))
                        {
                            // give up
                            return false;
                        }
                    }
                }
            }

            return $file;
        }   // end function findJQueryPlugin()
    }
}