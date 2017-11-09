<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2017 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Module
   @package         JSMgr

*/

if(!class_exists('JSMgr',false))
{
    final class JSMgr extends CAT_Addon_Tool
    {
        protected static $type        = 'tool';
        protected static $directory   = 'tool_JSMgr';
        protected static $name        = 'JSMgr';
        protected static $version     = '0.1';
        protected static $description = "JSMgr";
        protected static $author      = "BlackCat Development";
        protected static $guid        = "cae3b023-13a4-4a9d-8634-115cf4ee9bc2";
        protected static $license     = "GNU General Public License";
        protected static $basepath;

        /**
         *
         * @access public
         * @return
         **/
        public static function initialize()
        {
            self::$basepath = CAT_ENGINE_PATH.'/modules/lib_javascripts/plugins/';
        }   // end function initialize()

        /**
         *
         * @access public
         * @return
         **/
        public static function tool()
        {

            // handle upload
            if( CAT_Helper_Validate::sanitizePost('upload') && isset($_FILES['userfile']) && is_array($_FILES['userfile']) )
            {
                self::upload();
            }

            // get already installed plugins
            $plugins   = CAT_Helper_Directory::findDirectories(self::$basepath);
            $data      = self::db()->query(
                'SELECT * FROM `:prefix:addons_javascripts` ORDER BY `name`'
            );
            $installed = array();
            $added     = 0;

            if($data && is_object($data))
            {
                $data = $data->fetchAll();
                foreach($data as $i => $item)
                {
                    $installed[$item['name']] = $item;
                }
            }

            // check if all plugins are registered
            foreach($plugins as $name)
            {
                if(!array_key_exists($name,$installed))
                {
                    // slurp js
                    $jsfiles = CAT_Helper_Directory::findFiles(
                        self::$basepath.'/'.$name,
                        array(
                            'extension' => 'js',
                            'recurse'   => true
                        )
                    );
                    if(is_array($jsfiles) && count($jsfiles)>0)
                    {
                        $version  = null;
                        $isjquery = false;

                        foreach($jsfiles as $file)
                        {
                            try {
                                $content  = file_get_contents($file);
                                $version  = null;
                                $isjquery = false;
                                // try to find a version number
                                //$regexp   = '\/\*[^\*](\d+\.\d+(\.\d+)?(#\d+)?)\*\/';
                                $regexp = 'v?(\d+\.\d+(\.\d+)?)';
                                preg_match("~$regexp~i",$content,$m);
                                if(is_array($m) && isset($m[1]) && !empty($m[1]))
                                    $version = $m[1];
                                if(preg_match("~jquery~i",$content,$m)>0)
                                {
                                    $isjquery = true;
                                }
                                if($version) break;
                            } catch(Exception $e) {}
                        }

                        self::db()->query(
                            'INSERT INTO `:prefix:addons_javascripts` '
                            .'(`directory`,`name`,`version`,`jquery`) VALUES '
                            .'(:dir       ,:name ,:version ,:jq )',
                            array(
                                'dir'  => $name,
                                'name' => $name,
                                'version' => ( $version ? $version : 0 ),
                                'jq'      => ( $isjquery ? 'Y' : 'N' )
                            )
                        );
                        if(!self::db()->isError()) $added++;

                    }
                }
            }

            // reload data if any plugins where added
            if($added) {
                $data = self::db()->query(
                    'SELECT * FROM `:prefix:addons_javascripts` ORDER BY `name`'
                );
            }

            // get readmes (if available)
            $readmes = self::getReadmes($plugins);

            // render
            self::tpl()->setPath(CAT_ENGINE_PATH.'/modules/tool_JSMgr/templates/default','backend');
            return self::tpl()->get('tool',array('plugins'=>$data,'readmes'=>$readmes));
        }   // end function tool()

        /**
         * find files with name "readme_<lang>.html" or "readme.html" for
         * all directories in $plugins
         *
         * @access protected
         * @param  array     $plugins
         * @return array
         **/
        protected static function getReadmes($plugins)
        {
            $readme_filenames = array(
                // current language
                'readme_'.strtolower( LANGUAGE ).'.html',
                // default
                'readme.html'
            );
            $readmes = array();
            foreach($plugins as $p)
            {
                foreach($readme_filenames as $rfile)
                {
                    if(file_exists(self::$basepath.$p.'/'.$rfile))
                    {
                        $readmes[$p] = CAT_Helper_Validate::path2uri(self::$basepath.$p.'/'.$rfile);
                        break;
                    }
                    if(!isset($readmes[$p]))
                        $readmes[$p]='';
                }
            }
            return $readmes;
        }   // end function getReadmes()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function upload()
        {
            list($ok,$errors) = CAT_Helper_Upload::upload('userfile',CAT_ENGINE_PATH.'/temp');
            if(count($ok))
            {
                $filename = key($ok);
                $subdir   = $filename;

                // try to get version number from file name
                // highslide-5.0.0_2.zip
                $regexp = '^(.+?)[\s-_]?v?(\d+\.\d+(\.\d+)?)';
                preg_match("~$regexp~i",$subdir,$m);
                    /*
                    Array
                    (
                        [0] => highslide-5.0.0
                        [1] => highslide
                        [2] => 5.0.0
                        [3] => .0
                    )
                    */
                if(is_array($m) && isset($m[1]) && !empty($m[2]))
                {
                    if(isset($m[1]) && !empty($m[1])) $subdir = $m[1].'/'.$m[2];
                }
                $z = new CAT_Helper_Zip(CAT_ENGINE_PATH.'/temp/'.$filename);
                $z->config('Path',self::$basepath.'/'.$subdir);
                $z->extract();
            }
        }   // end function upload()
        
    }
}