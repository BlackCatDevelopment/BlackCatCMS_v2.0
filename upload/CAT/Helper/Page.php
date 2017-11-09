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

if (!class_exists('CAT_Helper_Page'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Page extends CAT_Object
    {
        /**
         * log level
         **/
        protected static $loglevel            = \Monolog\Logger::EMERGENCY;
        #protected static $loglevel            = \Monolog\Logger::DEBUG;
        /**
         * current instance (singleton pattern)
         **/
        private   static $instance            = NULL;
        /**
         * output template for meta tags
         **/
        private   static $meta_tpl            = '<meta %%content%% />';
        /**
         * output template for external stylesheets
         **/
        private   static $css_tpl             = '<link rel="stylesheet" href="%%file%%" media="%%media%%" />';
        /**
         *
         **/
        private   static $css_folders         = array('css','css/default');
        /**
         * output template for external javascripts
         **/
        private   static $js_tpl              = '%%condition_open%%<script type="text/javascript" src="%%file%%">%%code%%</script>%%condition_close%%';

        private   static $pages               = array();
        private   static $id_to_index         = array();
        private   static $pages_sections      = array();
        private   static $visibilities        = array();

        private   static $jquery_enabled      = false;
        private   static $jquery_seen         = false;
        private   static $jquery_ui_enabled   = false;
        private   static $jquery_ui_seen      = false;
        private   static $conditionals        = array();

        private   static $scan_paths          = array();
        // header js files
        private   static $js                  = array();
        // footer js files
        private   static $f_js                = array();
        // header static js
        private   static $header_js           = array();
        // js files having prerequisites
        private   static $prereq_js           = array();
        // already loaded files
        private   static $loaded              = array();

        private   static $meta                = array();
        private   static $css                 = array();
        private   static $title               = null;

        /**
         * the constructor loads the available pages from the DB and stores it
         * in internal arrays
         *
         * @access private
         * @return void
         **/
        public static function getInstance($skip_init=false)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                if(!$skip_init) self::init();
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * allow methods to be called as object
         **/
        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         * allows to add a CSS file programmatically
         *
         * @access public
         * @param  string  $url
         * @param  string  $media - default 'screen'
         * @return void
         **/
        public static function addCSS($url,$media='screen,projection')
        {
            if(!is_array(self::$css))
                self::$css = array();
            if(!isset(self::$css[$media]) || !is_array(self::$css[$media]))
                self::$css[$media] = array();
            self::$css[$media][] = $url;
        }   // end function addCSS()

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
        public static function addInc($file,$position=NULL)
        {
            if(!$position)
            {
                preg_match('~^(.*)s\.inc\.php$~i',pathinfo($file,PATHINFO_BASENAME),$m);
                $position = ( isset($m[1]) ? $m[1] : 'header' );
            }
            self::getIncludes($file,$position);
        }   // end function addInc()

        /**
         * allows to add a JS file programmatically
         *
         * @access public
         * @param  string  $url
         * @param  string  $pos   - 'header' (default) or 'footer'
         * @param  string  $after - optional; name of prerequisite script
         * @return void
         **/
        public static function addJS($url,$position='header',$after=NULL)
        {
            if($after)
            {
                if(!isset(self::$prereq_js[$after]) || !is_array(self::$prereq_js[$after]))
                    self::$prereq_js[$after] = array();
                self::$prereq_js[$after][] = $url;
            }
            else
            {
                if ($position == 'header')
                    $ref =& CAT_Helper_Page::$js;
                else
                    $ref =& CAT_Helper_Page::$f_js;
                $ref[] = $url;
            }
        }   // end function addJS()

        /**
         * allows to add meta tags at runtime
         *
         * Example:
         *     array(
         *         'name' => 'description',
         *         'content' => 'BlackCat CMS - '.$pg->lang()->translate('Administration')
         *     )
         *
         * @access public
         * @return
         **/
        public static function addMeta($array)
        {
            self::$meta[] = $array;
        }   // end function addMeta()

        /**
         * clear all (!) CSS info collected so far
         *
         * @access public
         * @return
         **/
        public static function clearCSS()
        {
            self::$css = array();
        }   // end function clearCSS()
        
        /**
         * checks if a page exists; checks access file and database entry
         *
         * @access public
         * @return
         **/
        public static function exists($id)
        {
            // search by ID
            if(is_numeric($id))
            {
                $page = self::properties($id);
                if($page && is_array($page) && count($page))
                    return true;
            }
            else
            {
                $sth = self::$instance->db()->query(
                    "SELECT `page_id` FROM `:prefix:pages` WHERE link=:link",
                    array('link'=>$id)
                );
                if ($sth->rowCount() > 0)
                    return true;
            }
            return false;
        }   // end function exists()

        /**
         * load headers|footers.inc.php
         *
         * @access public
         * @param  string  $for      - frontend (default) or backend
         * @param  string  $position - header (default) or footer
         * @param  string  $section  - optional
         * @return mixed
         **/
        public static function getAssets($position='header',$section=NULL,$ignore_inc=false)
        {
            $self  = self::$instance;

            // check params
            if(!in_array($position,array('header','footer')))
            {
                self::log()->addError(sprintf('invalid position [%s] passed',$position));
                return false;
            }

            // don't do this twice
            if ($position=='header' && defined('CAT_HEADERS_SENT'))
                return;

            // scan for headers/footers.inc?
            if(!$ignore_inc)
            {
                // find the paths to scan
                if(CAT_Backend::isBackend())
                {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Varianten
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    array_push(self::$scan_paths,CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME'));
                    // admin tool
                    if($self->router()->match('~\/tool\/~i'))
                    {
                        $tool = CAT_Backend_Admintools::getTool();
                        array_push(self::$scan_paths,CAT_ENGINE_PATH.'/modules/'.$tool);
                        array_push(self::$scan_paths,CAT_ENGINE_PATH.'/modules/tool_'.$tool);
                    }
                }
                else
                {
                    array_push(self::$scan_paths,CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('DEFAULT_TEMPLATE'));
                }

                // sections
                $page_id = CAT_Page::getID();
                if($page_id)
                {
                    $sections = CAT_Sections::getSections($page_id);
                    if(count($sections))
                    {
                        foreach($sections as $block => $items)
                        {
                            foreach($items as $item)
                            {
                                array_push(self::$scan_paths,CAT_ENGINE_PATH.'/modules/'.$item['module']);
                            }
                        }
                    }
                }

                // load *.inc.php
                self::$scan_paths = array_unique(self::$scan_paths);
                foreach(array_values(self::$scan_paths) as $path)
                {
                    $file = CAT_Helper_Directory::sanitizePath($path.'/'.$position.'s.inc.php');
                    if(file_exists($file))
                    {
                        self::getIncludes($file,$position);
                    }
                }
            }

            // add backend area / region files
            if(CAT_Backend::isBackend())
            {
                $area = CAT_Backend::getArea();
                self::log()->addDebug(sprintf(
                    'looking for area specific js/css, current area: [%s]',
                    $area
                ));
                foreach(array_values(self::$scan_paths) as $path)
                {
                    if($position=='header')
                    {
                        foreach(self::$css_folders as $folder)
                        {
                            $cssfile = CAT_Helper_Directory::sanitizePath($path.'/'.$folder.'/backend.css');
                            if(file_exists($cssfile))
                            {
                                self::log()->addDebug(sprintf(
                                    'adding CSS file: [%s]',
                                    $cssfile
                                ));
                                self::addCSS(self::checkPath(CAT_Helper_Validate::path2uri($cssfile)));
                            }
                        }

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Variante
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        $cssfile = CAT_Helper_Directory::sanitizePath($path.'/css/default/'.$area.'.css');
                        $jsfile  = CAT_Helper_Directory::sanitizePath($path.'/js/'.$area.'.js');
                        // css in header only
                        if(file_exists($cssfile))
                        {
                            self::log()->addDebug(sprintf(
                                'adding CSS file: [%s]',
                                $cssfile
                            ));
                            self::addCSS(self::checkPath(CAT_Helper_Validate::path2uri($cssfile)));
                        }
                    }
                    else
                    {
                        $jsfile = CAT_Helper_Directory::sanitizePath($path.'/js/'.$area.'_body.js');
                    }
                    if(file_exists($jsfile))
                    {
                        self::log()->addDebug(sprintf(
                            'adding JS file: [%s], position [%s]',
                            $jsfile,$position
                        ));
                        self::addJS(self::checkPath(CAT_Helper_Validate::path2uri($jsfile)),$position);
                    }
                }
            }

            $output = '';
            if ($position=='header')
            {
                define('CAT_HEADERS_SENT',true);
                $output = self::renderMeta()
                        . self::renderCSS();
            }
            $output .= self::renderJS($position);
            return $output;
        }   // end function getAssets()

        /**
         * determine default page
         *
         * @access public
         * @return void
         **/
        public static function getDefaultPage()
        {
            if(!count(self::$pages))
                self::init();

            // for all pages with level 0...
            $root    = array();
            $now     = time();
            $ordered = CAT_Helper_Array::sort(self::$pages,'position');

            foreach($ordered as $page)
            {
                if (
                       $page['level']      == 0
                    && $page['visibility'] == 'public'
                    && self::isActive($page['page_id'])
                ) {
                    if(!CAT_Registry::get('PAGE_LANGUAGES')===true || $page['language'] == CAT_Registry::get('LANGUAGE'))
                    {
                        return $page['page_id'];
                    }
                }
            }
            // no page so far, return first visible page on level 0
            foreach( $ordered as $page )
            {
                if (
                       $page['level'] == 0
                    && $page['visibility'] == 'public'
                    && self::isActive($page['page_id'])
                ) {
                    return $page['page_id'];
                }
            }
            // no page
            return false;
        } // end function getDefaultPage()

        /**
         *
         * @access public
         * @return
         **/
        public static function getExtraHeaderFiles($page_id=NULL)
        {
            $data = array(); //'js'=>array(),'css'=>array(),'code'=>''
            $q    = 'SELECT * FROM `:prefix:pages_headers` WHERE `page_id`=:page_id';
            $r    = CAT_Object::db()->query($q,array('page_id'=>$page_id));
            $data = $r->fetchAll();

            foreach($data as $i => $row)
            {
                if(isset($row['page_js_files']) && $row['page_js_files']!='')
                    $data[$i]['js'] = unserialize($row['page_js_files']);
                if(isset($row['page_css_files']) && $row['page_css_files']!='')
                    $data[$i]['css'] = unserialize($row['page_css_files']);
            }

            return $data;
        }   // end function getExtraHeaderFiles()

        /**
         * creates a full url for the given pageID
         *
         * @access public
         * @params integer  $page_id
         * @return string
         **/
        public static function getLink($page_id)
        {
            if(!is_numeric($page_id))
                $link = $page_id;
            else
                $link = self::properties($page_id,'link');

            if(!$link)
                return NULL;

            // Check for :// in the link (used in URL's) as well as mailto:
            if (strstr($link, '://') == '' && substr($link, 0, 7) != 'mailto:')
                return CAT_URL.$link.CAT_Registry::get('PAGE_EXTENSION');
            else
                return $link;

        }   // end function getLink()

        /**
         * get a list of pages in other languages that are linked to the
         * given page; returns an array of pageIDs or boolean false if no
         * linked pages are found
         *
         * @access public
         * @param  integer  $page_id
         * @return mixed
         **/
        public static function getLinkedByLanguage($page_id)
        {
            $sql     = 'SELECT * FROM `:prefix:pages_langs` AS t1'
                     . ' RIGHT OUTER JOIN `:prefix:pages` AS t2'
                     . ' ON `t1`.`link_page_id`=`t2`.`page_id`'
                     . ' WHERE `t1`.`page_id` = :id'
                     ;

            $results = self::getInstance()->db()->query($sql,array('id'=>$page_id));
            if ($results->rowCount())
            {
                $items = array();
                while (($row = $results->fetch()) !== false)
                {
                    $row['href'] = self::getLink($row['link']) . (($row['lang'] != '') ? '?lang=' . $row['lang'] : NULL);
                    $items[]     = $row;
                }
                return $items;
            }
            return false;
        }   // end function getLinkedByLanguage()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPageForRoute($route)
        {
            if(CAT_Backend::isBackend()) return 0;
            // remove suffix from route
            $route  = str_ireplace(CAT_Registry::get('PAGE_EXTENSION'), '', $route);
            // remove trailing /
            $route  = rtrim($route,"/");
            // add / to front
            if(substr($route,0,1) !== '/') $route = '/'.$route;
            // find page in DB
            $result = self::db()->query(
                'SELECT `page_id` FROM `:prefix:pages` WHERE `link`=?',
                array($route)
            );
            $data   = $result->fetch();
            if(!$data || !is_array($data) || !count($data))
                CAT_Page::print404();
            else
                return $data['page_id'];
        }   // end function getPageForRoute()


        /**
         * get properties for page $page_id
         *
         * @access public
         * @param  integer  $page_id
         * @param  string   $type
         * @param  string   $key
         * @return
         **/
        public static function getPageSettings($page_id,$type='internal',$key=NULL)
        {
            $set = self::properties($page_id,'settings');
            if($type)
            {
                if($key)
                {
                    if( isset($set[$type][$key]) )
                    {
                        if(is_array($set[$type][$key]) && count($set[$type][$key]) == 1)
                            return $set[$type][$key][0];
                        return $set[$type][$key];
                    }
                    else
                    {
                        return NULL;
                    }
                }
                else
                {
                    return ( isset($set[$type]) ? $set[$type] : NULL );
                }
            }
            return $set;
        }   // end function getPageSettings()

        /**
         * returns complete pages array
         *
         * @access public
         * @param  boolean $all - show all pages or only visible (default:false)
         * @return array
         **/
        public static function getPages($all=false)
        {
            if(!count(self::$pages)) self::getInstance();
            if($all)
            {
                $pages =  self::$pages;
            } else {
                // only visible for current lang
                $pages = array();
                foreach(self::$pages as $pg)
                    if(self::isVisible($pg['page_id']))
                        $pages[] = $pg;
            }
            return $pages;
        }   // end function getPages()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPagesAsList($all=false)
        {
            $pages = self::getPages($all);
            // sort by children
            $pages = self::lb()->sort($pages);
            $list  = array(0=>self::lang()->translate('none'));
            foreach($pages as $p) {
                $list[$p['page_id']] = str_repeat('|-- ',$p['level']) . $p['menu_title'];
            }
            return $list;
        }   // end function getPagesAsList()
        

        /**
         *
         * @access public
         * @return
         **/
        public static function getPagesForLanguage($lang)
        {
            if(!count(self::$pages)) self::getInstance();
            $result = array();
            foreach(self::$pages as $pg)
            {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Achtung: isVisible() funktioniert nicht richtig, wenn der Benutzer im BE
// angemeldet ist, jedoch per AJAX z.B. CAT_Backend::list() aufgerufen wird
// Daher erst mal zum Testen auskommentiert
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                if($pg['language']==$lang) // && self::isVisible($pg['page_id']) )
                    $result[] = $pg;
            }
            return $result;
        }   // end function getPagesForLanguage()

        /**
         * returns pages array for given menu number
         *
         * @access public
         * @param  integer  $id    - menu id
         * @return array
         **/
        public static function getPagesForMenu($id)
        {
            if(!count(self::$pages)) self::getInstance();
            $menu = array();
            foreach(self::$pages as $pg)
            {
                if( $pg['menu'] == $id && self::isVisible($pg['page_id']) )
                    $menu[] = $pg;
            }
            return $menu;
        }   // end function getPagesForMenu()

        /**
         * returns the template for the given page
         *
         * @access public
         * @param  integer  $page_id
         * @return string
         **/
        public static function getPageTemplate($page_id)
        {
            $tpl = self::properties($page_id,'template');
            return ( $tpl != '' ) ? $tpl : CAT_Registry::get('DEFAULT_TEMPLATE');
        }   // end function getPageTemplate()

        /**
         * get the path of the given page
         *
         * @access public
         * @param  integer  $page_id
         * @param  boolean  $skip_zero
         * @param  boolean  $as_array
         * @return mixed
         **/
        public static function getPageTrail($page_id,$skip_zero=false,$as_array=false)
        {
            $ids = array_reverse(self::getParentIDs($page_id));
            if($skip_zero) array_shift($ids);
            $ids[] = $page_id;
            return (
                $as_array ? $ids : implode(',',$ids)
            );
        }   // end function getPageTrail()

        /**
         *
         * @access public
         * @return
         **/
        public static function getPageTypes()
        {
            return array(
                'page' => 'Page',
                'menu_link' => 'Menu Link',
            );
        }   // end function getPageTypes()

        /**
         * resolves the path to root and returns the list of parent IDs
         *
         * @access public
         * @return
         **/
        public static function getParentIDs($page_id)
        {
            $ids = array();
            while(self::properties($page_id,'parent') !== NULL)
            {
                if ( self::properties($page_id,'level') == 0 )
                    break;
                $ids[]   = self::properties($page_id,'parent');
                $page_id = self::properties($page_id,'parent');
            }
            return $ids;
        }   // end function getParentIDs()

        /**
         * returns the root level page of a trail
         *
         * @access public
         * @return integer
         **/
        public static function getRootParent($page_id)
        {
            if(self::properties($page_id,'level')==0)
                return 0;
            $trail = self::getPageTrail($page_id,false,true);
            return $trail[0];
        }   // end function getRootParent()

        /**
         *
         * @access public
         * @return
         **/
        public static function getVisibilities()
        {
            if(!count(self::$visibilities))
            {
                $sth = self::$instance->db()->query(
                    'SELECT * FROM `:prefix:visibility`'
                );
                $temp = $sth->fetchAll();
                foreach($temp as $item)
                {
                    self::$visibilities[$item['vis_id']] = $item['vis_name'];
                }
            }
            return self::$visibilities;
        }   // end function getVisibilities()
        
        /**
         * checks if page is active (=has active sections and is between
         * publ_start and publ_end)
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isActive($page_id)
        {
            if(self::isDeleted($page_id))
                return false;
            $sections = CAT_Sections::getSections($page_id,null,true);
            if(count($sections))
                return true;
            return false;
        } // end function isActive()

        /**
         * checks if page is deleted
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isDeleted($page_id)
        {
            $page    = self::properties($page_id);
            if($page['vis_id']==5)
                return true;
            return false;
        } // end function isDeleted()

        /**
         * Check whether a page is visible or not
         * This will check page-visibility, user- and group permissions
         *
         * @access public
         * @param  integer  $page_id
         * @return boolean
         **/
        public static function isVisible($page_id)
        {
            $show_it = false;
            $page    = self::properties($page_id);

            switch ($page['vis_id'])
            {
                // public - always visible
                case 1:
                    $show_it = true;
                    break;
                // none, deleted - never shown in FE
                case 4:
                case 5:
                    $show_it = false;
                    break;
                // hidden - shown if called, but not in menu; skip intro page (selectPage(true))
                case 3:
                    if(CAT_Page::getID()==$page_id)
                        $show_it = true;
                    break;
                // private, registered - shown if user is allowed
                case 2:
                case 6:
                    if (CAT_User::getInstance()->is_authenticated() == true)
                    {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: ANPASSEN FUER NEUES BERECHTIGUNGSZEUGS
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
/*
                        // check language
                        if(CAT_Registry::get('PAGE_LANGUAGES')=='false'||(self::properties($page_id,'language')==''||self::properties($page_id,'language')==LANGUAGE))
                        $show_it = (
                               CAT_Users::is_group_match(CAT_Users::get_groups_id(), $page['viewing_groups'])
                            || CAT_Users::is_group_match(CAT_Users::get_user_id(), $page['viewing_users'])
                            || CAT_Users::is_root()
                        );
*/
                    }
                    else
                    {
                        $show_it = false;
                    }
                    break;
            }
            return $show_it;
        } // end function isVisible()

        /**
         * returns the properties for the given page ID
         *
         * @access public
         * @param  integer $page_id
         * @param  string  $key      - optional property name
         * @return mixed
         **/
        public static function properties($page_id=NULL,$key=NULL)
        {
            if(!$page_id)
                $page_id = CAT_Page::getID();

            if(!count(self::$pages) && !CAT_Registry::exists('CAT_HELPER_PAGE_INITIALIZED'))
                self::init();

            // get page data
            $page = isset(self::$id_to_index[$page_id])
                  ? self::$pages[self::$id_to_index[$page_id]]
                  : NULL;

            if(count($page))
            {
                if($key)
                {
                    if(isset($page[$key]))
                    return $page[$key];
                    else
                        return NULL;
                }
                else
                {
                    return $page;
                }
            }
            return NULL;
        }   // end function properties()

        /**
         * returns the items of static array $css as HTML link markups
         *
         * @access public
         * @return HTML
         **/
        public static function renderCSS($as_array=false)
        {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Conditionals
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $output = array();
            if(count(self::$css))
            {
                foreach(array_keys(self::$css) as $media)
                {
                    $files = self::$css[$media];
                    foreach($files as $i => $file)
                        $files[$i] = preg_replace('~^/~','',$file); // remove leading /
                    $line = str_replace(
                        array('%%file%%','%%media%%'),
                        array(CAT_Helper_Assets::serve('css',$files),$media),
                        self::$css_tpl
                    );
                    $output[] = $line;
                }
            }
            return implode("\n",$output);
        }   // end function renderCSS()

        /**
         *
         * @access public
         * @return
         **/
        public static function renderJS($position='header')
        {
            // reference the appropriate JS array (header or footer)
            if ($position == 'header')
            {
                $ref =& self::$js;
                $js  = ( CAT_Backend::isBackend() ? 'backend' : 'frontend' );
            }
            else
            {
                $ref =& self::$f_js;
                $js  = ( CAT_Backend::isBackend() ? 'backend' : 'frontend' ).'_body';
            }

            self::$scan_paths = array_unique(self::$scan_paths);

            // add backend|frontend[_body].js
            foreach (self::$scan_paths as $directory)
            {
                foreach(array_values(array('js','')) as $subdir)
                {
                    $file = CAT_Helper_Directory::sanitizePath($directory.'/'.$subdir.'/'.$js.'.js');
                    if (file_exists($file))
                    {
                        $ref[] = CAT_Helper_Validate::path2uri($file);
                    }
                }
            }

            $ref       = array_unique($ref);
            $output    = NULL;
            $cond_open = false;
            $condition = false;
            $local     = array();
            $remote    = array();

            if(count($ref))
            {
                foreach($ref as $i => $file)
                {
                    if(preg_match('~CONDITIONAL (.*) START$~',$file,$m)) // opening
                    {
                        $output   .= self::renderFiles('js',$local,true,$cond_open,$condition)
                                  .  self::renderFiles('js',$remote,false,$cond_open,$condition);
                        $condition = $m[1];
                        $cond_open = true;
                        $local     = array();
                        $remote    = array();
                        continue;
                    }
                    if(preg_match('~CONDITIONAL (.*) END~',$file,$m)) // closing
                    {
                        $condition = $m[1];
                        $output   .= self::renderFiles('js',$local,true,$cond_open,$condition)
                                  .  self::renderFiles('js',$remote,false,$cond_open,$condition);
                        $local     = array();
                        $remote    = array();
                        $cond_open = false;
                        continue;
                    }
                    if(!preg_match('~^http(s)?://~i',$file)) // it's a local file
                    {
                        $local[]  = preg_replace('~^/~','',$file);
                        self::$loaded[] = pathinfo($file,PATHINFO_BASENAME);
                    }
                    else
                    {
                        $remote[] = $file;
                    }
                }
            }

            // add js having prerequisites
            if(count(self::$prereq_js))
            {
                self::log()->addDebug(sprintf(
                    'checking [%d] prerequisites', count(self::$prereq_js)
                ));
                foreach(self::$prereq_js as $required => $urls)
                {
                    self::log()->addDebug(sprintf(
                        '    checking required [%s]', $required
                    ));
                    if(in_array($required,self::$loaded))
                    {
                        self::log()->addDebug(sprintf(
                            '        found, adding [%d] urls', count($urls)
                        ));
                        foreach($urls as $file)
                            $local[] = preg_replace('~^/~','',$file);
                    }
                }
            }

            $output .= self::renderFiles('js',$local,true,$cond_open,$condition)
                    .  self::renderFiles('js',$remote,false,$cond_open,$condition);

            if($position=='header')
            {
                // add static js
                self::$header_js[] = 'var CAT_URL = "'.CAT_URL.'";';
                if(CAT_Backend::isBackend())
                {
                    array_push(
                        self::$header_js,
	                    'var CAT_ADMIN_URL = "'.CAT_ADMIN_URL. '";'
                    );
                }
                $output = str_replace(
                    array('%%condition_open%%',' src="%%file%%"','%%code%%','%%condition_close%%'),
                    array('','',implode("\n",self::$header_js),''),
                    self::$js_tpl
                ) . $output;
            }

            return $output;
        }   // end function renderJS()

        /**
         *
         * @access public
         * @return
         **/
        public static function renderMeta($droplets_config=array())
        {
            $output = array();
            $title  = null;

            // check global meta array
            if(is_array(self::$meta) && count(self::$meta))
            {
                foreach(self::$meta as $el)
                {
                    if(!is_array($el) || !count($el)) continue;
                    $str = '<meta ';
                    foreach($el as $key => $val)
                        $str .= $key.'="'.$val.'" ';
                    $str .= '/>';
                    $output[] = $str;
                }
            }
            $output = array_unique($output);

            // Frontend only: get page properties
            if(!CAT_Backend::isBackend())
            {
                $properties = self::properties(CAT_Page::getID());

                // droplets may override page title and description and/or
                // add meta tags

                // check page title
                if(isset($droplets_config['page_title']))
                    $title = $droplets_config['page_title'];
                elseif(self::$title)
                    $title = self::$title;
                elseif(defined('WEBSITE_TITLE'))
                    $title = WEBSITE_TITLE . (isset($properties['page_title']) ? ' - ' . $properties['page_title'] : '' );
                elseif(isset($properties['page_title']))
                    $title = $properties['page_title'];
                else
                    $title = '-';

                // check description
                if(isset($droplets_config['description']))
                    $description = $droplets_config['description'];
                elseif(isset($properties['description']) && $properties['description'] != '' )
                    $description = $properties['description'];
                else
                    $description = CAT_Registry::get('WEBSITE_DESCRIPTION');

                // check other meta tags set by droplets
                if(isset($droplets_config['meta']))
                    $output[] = $droplets_config['meta'];

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: SEO
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            }
            else {
                $description = CAT_Registry::get('WEBSITE_DESCRIPTION');
                if(self::$title)
                    $title = self::$title;
            }

            if($title)
                $output[] = '<title>' . $title . '</title>';
            if ($description!='')
                $output[] = '<meta name="description" content="' . $description . '" />';

            // favicons
            $favs = CAT_Backend_Favicon::findFiles(1);
            if(is_array($favs) && count($favs))
            {
                foreach($favs as $group => $items)
                {
                    switch($group)
                    {
                        case 'apple':
                            foreach($items as $item => $size) {
                                $output[] = '<link rel="apple-touch-icon" sizes="'.$size.'" href="'.CAT_URL.'/'.$item.'" />';
                            }
                            break;
                        case 'desktop':
                            foreach($items as $item => $size) {
                                $type = pathinfo($item,PATHINFO_EXTENSION);
                                $output[] = '<link rel="icon" type="'
                                          . ($type=='ico' ? 'image/vnd.microsoft.icon' : 'image/png')
                                          . '" href="'.CAT_URL.'/'.$item.'" sizes="'.$size.'" />';
                            }
                            break;
                        case 'windows':
                            if(array_key_exists('browserconfig.xml',$items))
                            {
                                $output[] = '<meta name="msapplication-config" content="'.CAT_URL.'/browserconfig.xml" />';
                                unset($items['browserconfig.xml']);
                            }
                            else
                            {
                                $output[] = '<meta name="msapplication-config" content="none" />';
                            }
                            foreach($items as $item => $size) {
                                if($size=='144x144') {
                                    $output[] = '<meta name="msapplication-TileImage" content="'
                                              . CAT_URL.'/mstile-144x144.png" />';
                                } else {
                                    $output[] = '<meta name="msapplication-'
                                              . ($size=='310x150' ? 'wide' : 'square')
                                              . $size.'logo" content="'.CAT_URL.'/'.$item.'" />';
                                }
                            }
                            $tilecolor = self::getSetting('favicon_tilecolor');
                            if(strlen($tilecolor)) {
                                $output[] = '<meta name="msapplication-TileColor" content="#'.$tilecolor.'" />';
                            }
                            $appname = self::getSetting('app_name');
                            if(strlen($appname)) {
                                $output[] = '<meta name="application-name" content="'
                                          . $appname
                                          . (CAT_Backend::isBackend() ? ' - '.self::lang()->t('Administration') : '')
                                          . '" />';
                            }
                            break;
                    }
                }
            }

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO:
/*
    <link rel="apple-touch-icon" href="/bootstrap/img/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/bootstrap/img/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/bootstrap/img/apple-touch-icon-114x114.png">
*/
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            // if the current page has linked pages in other languages
            $linked = array();
            //$linked = self::getLinkedByLanguage($page_id);
            if(is_array($linked) && count($linked))
            {
                foreach($linked as $page)
                {
                    $output[] = '<link rel="alternate" hreflang="'.strtolower($page['lang']).'" href="'.self::getLink($page['page_id']).'">';
                }
            }

            return implode("\n",array_unique($output));
        } // end function renderMeta()

        /**
         * allows to set the page title for the current page
         *
         * @access public
         * @return
         **/
        public static function setTitle($title)
        {
            self::$title = $title;
        }   // end function setTitle()
        
        /**
         * check for allowed paths (CAT, templates, modules) inside engine
         * folder
         *
         * @access private
         * @param  string   $path
         * @return mixed    string on success, false on invalid path
         **/
        private static function checkPath($path)
        {
            $path  = preg_replace('~^/~','',$path); // remove leading /
            $parts = explode('/',$path);
            // path starting with 'plugins'? -> lib_javascript
            if($parts[0]=='plugins')
            {
                return CAT_Helper_Directory::sanitizePath('modules/lib_javascript/'.implode('/',$parts));
            }
            if(in_array($parts[0],array('CAT','templates','modules')))
            {
                //array_shift($parts); // remove?
                return CAT_Helper_Directory::sanitizePath(implode('/',$parts));
            }
            if(preg_match('~^https?\:~i',$path))
                return CAT_Helper_Validate::sanitize_url($path);
            return false;
        }   // end function checkPath()
        
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

        /**
         * analyzes the contents of the headers.inc.php
         *
         * @access private
         * @param  string  $file - path to headers.inc.php
         * @param  string  $for  - frontend | backend
         * @return void
         **/
        private static function getIncludes($file,$position='header')
        {
            $self = self::getInstance();
            $file = CAT_Helper_Directory::sanitizePath($file);

            // load file
            self::log()->addDebug(sprintf('loading file [%s], position [%s]',$file,$position));
            require $file;

            $array   =& ${'mod_'.$position.'s'};
            $for     =  ( CAT_Backend::isBackend() ? 'backend' : 'frontend' );
            $page_id =  CAT_Page::getID();

            // if there are any entries...
            if (isset($array[$for]) && is_array($array[$for]) && count($array[$for]))
            {
                // reference the appropriate JS array (header or footer)
                if ($position == 'header') $ref =& self::$js;
                else                       $ref =& self::$f_js;

                if($position=='header') // header only
                {
                    $self->log()->addDebug('checking META');
                    // ----- check META -----
                    if(    isset($array[$for]['meta'])
                        && is_array($array[$for]['meta'])
                        && count($array[$for]['meta'])
                    ) {
                        $arr =& $array[$for]['meta']; // shorter :)
                        $self->log()->addDebug(sprintf('   There are [%d] meta entries',count($arr)));
                        foreach($arr as $el)
                        {
                            if(!is_array($el) || !count($el)) continue;
                            $str = '';
                            foreach($el as $key => $val)
                                $str .= $key.'="'.$val.'" ';

                            self::$meta[] = str_replace(
                                '%%content%%',
                                $str,
                                self::$meta_tpl
                            );
                        }
                    }
                    $self->log()->addDebug('checking CSS');
                    // ----- check CSS -----
                    if(isset($array[$for]['css']) && is_array($array[$for]['css']) && count($array[$for]['css']))
                    {
                        $self->log()->addDebug(sprintf('   There are [%d] css entries',count($array[$for]['css'])));
                        // check the paths
                        foreach($array[$for]['css'] as $item)
                        {
                            if(isset($item['file'])) // skip invalid entries
                            {
                                $media = (isset($item['media']) ? $item['media'] : 'screen,projection');
                                if(!isset(self::$css[$media])) self::$css[$media] = array();
                                if(false!==($file=self::checkPath($item['file'])))
                                {
                                    if(isset($item['conditional']) && $item['conditional'] != '')
                                    {
                                        $file = '<!--[if '.$item['conditional'].']>'."\n"
                                              . $file
                                              . '<![endif]-->'."\n"
                                              ;
                                    }
                                    self::$css[$media][] = $file;
                                }
                            }
                        }
                    }
                }   // end if($position=='header')

                $self->log()->addDebug('checking jQuery components');
                // ----- check jQuery components -----
                if (isset($array[$for]['jquery']) && is_array($array[$for]['jquery']) && count($array[$for]['jquery']) && !self::$jquery_seen)
                {
                    $self->log()->addDebug(sprintf('   There are [%d] jQuery entries',count($array[$for]['jquery'])));
                    $arr = $array[$for]['jquery']; // shorter :)
                    // scan for plugins
                    if (isset($arr['plugins']) && is_array($arr['plugins']))
                    {
                        $self->log()->addDebug(sprintf('   There are [%d] jQuery plugins to be loaded',count($arr['plugins'])));
                        foreach ($arr['plugins'] as $item)
                        {
                            if(false!==($file=self::findJQueryPlugin($item)))
                            {
                                $ref[] = $file;
                            }
                        }
                    }
                    if(
                           isset($arr['ui'])
                        && $arr['ui'] === true
                    ) {
                        self::$jquery_ui_enabled = true;
                        self::$jquery_enabled = true;
                    }
                    if(
                           !self::$jquery_enabled && count($ref)
                        || (isset($arr['core']) && $arr['core'] === true)
                    ) {
                        self::$jquery_enabled = true;
                    }
                }   // end if (isset($array[$for]['jquery']))

                // ----- other JS -----
                if(isset($array[$for]['js']) && is_array($array[$for]['js']) && count($array[$for]['js']))
                {
                    $temp_arr = ( is_array($array[$for]['js'][0]) ? $array[$for]['js'][0] : $array[$for]['js'] );
                    foreach(array_values($temp_arr) as $item)
                    {
                        if(is_array($item))
                        {
                            // if it's an array there _must_ be a conditional
                            if(!isset($item['conditional'])) continue;
                            $ref[] = 'CONDITIONAL '.$item['conditional'] . ' START';
                            foreach($item['files'] as $f) $ref[] = $f;
                            $ref[] = 'CONDITIONAL '.$item['conditional'] . ' END';
                        }
                        else
                        {
                            if(false!==($file=self::checkPath($item)))
                            {
                                $ref[] = $file;
                            }
                        }
                    }
                }   // end if(isset($array[$for]['js']))

                if(self::$jquery_ui_enabled && !self::$jquery_ui_seen)
                {
                    array_unshift($ref,'/modules/lib_javascript/jquery-ui/ui/jquery-ui.min.js');
                    array_unshift($ref,'/modules/lib_javascript/jquery-ui/ui/i18n/jquery-ui-i18n.min.js');
                    self::$jquery_ui_seen = true;
                }
                if(self::$jquery_enabled && !self::$jquery_seen)
                {
                    array_unshift($ref,'/modules/lib_javascript/jquery-core/jquery-core.min.js');
                    self::$jquery_seen = true;
                }
            }
            else
            {
                $self->log()->addDebug(sprintf('no $array for [%s]',$for));
            }

            return ( isset($ref) ? $ref : false );
        }   // end function getIncludes()

        /**
         * initialize; fills the internal pages array
         *
         * @access private
         * @param  boolean $force - always reload
         * @return void
         **/
        private static function init($force=false)
        {
            if(CAT_Registry::exists('CAT_HELPER_PAGE_INITIALIZED') && !$force)
                return;

            if(!self::$instance) self::getInstance(true);

            // fill pages array
            if(count(self::$pages)==0 || $force)
            {
                $result = self::$instance->db()->query(
                      'SELECT `t1`.*, `t2`.`vis_name` AS `visibility` '
                    . 'FROM `:prefix:pages` AS `t1` '
                    . 'JOIN `:prefix:visibility` AS `t2` '
                    . 'ON `t1`.`vis_id`=`t2`.`vis_id` '
                    . 'ORDER BY `level` ASC, `position` ASC'
                );
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO:
//     Infos zu is_in_trail etc fehlen noch
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                self::$pages = $result->fetchAll();
                // map index to page id
                foreach(self::$pages as $index => $page)
                {
                    // note: order is important! $id_to_index first!
                    self::$id_to_index[$page['page_id']] = $index;
                    self::$pages[$index]['href'] = self::getLink($page['page_id']);
                }
            }

            CAT_Registry::register('CAT_HELPER_PAGE_INITIALIZED',true);
        }   // end function init()

        /**
         *
         * @access private
         * @return
         **/
        private static function renderFiles($type,$files,$is_local=false,$cond_open=false,$condition=NULL)
        {
            if(!count($files)) return;
            if($is_local)
            {
                return str_replace(
                    array('%%condition_open%%','%%file%%','%%code%%','%%condition_close%%'),
                    array(
                        ($cond_open ? '<!--[if '.$condition.']>' : ''),
                        CAT_Helper_Assets::serve($type,$files),
                        '',
                        ($cond_open ? '<![endif]-->' : '')
                    ),
                    self::$js_tpl
                );
            }
            else
            {
                $output = '';
                foreach($files as $i => $rfile)
                {
                    $output .= "\n".str_replace(
                        array('%%condition_open%%','%%file%%','%%code%%','%%condition_close%%'),
                        array(
                            ($i==0 ? '<!--[if '.$condition.']>'."\n" : ''),
                            $rfile,
                            '',
                            ($i==(count($files)-1) ? "\n".'<![endif]-->'."\n" : '')
                        ),
                        self::$js_tpl
                    );
                }
                return $output;
            }
        }   // end function renderFiles()
        
    }   // end class CAT_Helper_Page
}
