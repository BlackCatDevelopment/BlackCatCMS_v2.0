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

namespace CAT\Addon;

use \CAT\Base as Base;

if(!class_exists('JSMgr',false))
{
    final class JSMgr extends Tool
    {
        protected static $type        = 'tool';
        protected static $directory   = 'JSMgr';
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
            self::$basepath = \CAT\Helper\Directory::sanitizePath(
                CAT_ENGINE_PATH.'/modules/lib_javascript/plugins/'
            );
        }   // end function initialize()

        /**
         *
         * @access public
         * @return
         **/
        public static function tool()
        {

            // handle upload
            if( \CAT\Helper\Validate::sanitizePost('upload') && isset($_FILES['userfile']) && is_array($_FILES['userfile']) )
            {
                self::upload();
            }

            // get already installed plugins
            $plugins   = \CAT\Helper\Directory::findDirectories(self::$basepath);
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
                    $jsfiles = \CAT\Helper\Directory::findFiles(
                        self::$basepath.'/'.$name,
                        array(
                            'extension' => 'js',
                            'recurse'   => true
                        )
                    );
                    // find readme(s)
                    $readmes = \CAT\Helper\Directory::findFiles(
                        self::$basepath.'/'.$name,
                        array(
                            'extension' => 'html',
                            'recurse'   => true
                        )
                    );
                    // analyze JS files
                    if(is_array($jsfiles) && count($jsfiles)>0)
                    {
                        $version  = null;
                        $isjquery = false;
                        $readme   = null;

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

                        if(is_array($readmes) && count($readmes)>0) {
                            // get the first one
                            $readme = str_ireplace(
                                self::$basepath.'/'.$name.'/',
                                '',
                                $readmes[0]
                            );
                        }

                        self::db()->query(
                            'INSERT INTO `:prefix:addons_javascripts` '
                            .'(`directory`,`name`,`version`,`jquery`,`readme`) VALUES '
                            .'(:dir       ,:name ,:version ,:jq     , :readme)',
                            array(
                                'dir'     => $name,
                                'name'    => $name,
                                'version' => ( $version ? $version : 0 ),
                                'jq'      => ( $isjquery ? 'Y' : 'N' ),
                                'readme'  => $readme
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
            // \CAT\Helper\Validate::path2uri(self::$basepath.$p.'/'.$rfile);

            // render
            self::tpl()->setPath(CAT_ENGINE_PATH.'/modules/tool_JSMgr/templates/default','backend');
            return self::tpl()->get('tool',array('plugins'=>$data,'baseuri'=>\CAT\Helper\Validate::path2uri(self::$basepath)));
        }   // end function tool()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function upload()
        {
            list($ok,$errors) = \CAT\Helper\Upload::upload('userfile',CAT_ENGINE_PATH.'/temp');
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
                $z = new \CAT\Helper\Zip(CAT_ENGINE_PATH.'/temp/'.$filename);
                $z->config('Path',self::$basepath.'/'.$subdir);
                $z->extract();
            }
        }   // end function upload()
        
    }
}