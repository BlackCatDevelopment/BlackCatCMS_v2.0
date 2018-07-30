<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

namespace CAT\Helper;
use \CAT\Base as Base;

if(!class_exists('\CAT\Helper\Menu',false))
{
	class Menu extends Base
	{
        protected static $loglevel  = \Monolog\Logger::EMERGENCY;
        /**
         * holds local instance
         **/
        private   static $instance;
        /**
         * holds local list builder instance
         **/
        private   static $list      = NULL;
        /**
         * this maps some settings to shorter aliases
         **/
        private   static $alias_map = array(
            'prefix'       => 'css_prefix',
            'first'        => 'first_li_class',
            'last'         => 'last_li_class',
            'child'        => 'has_child_li_class',
            'current'      => 'current_li_class',
            'open'         => 'is_open_li_class',
            'closed'       => 'is_closed_li_class',
        );

        /**
         * create a singular instance (for object oriented use)
         **/
        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * for object oriented use
         **/
        public function __call($method, $args)
            {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         * we need our own list helper here because we will change some
         * global settings
         **/
        protected static function listbuilder($reset=false)
        {
            if(!self::$list)
            {
                self::$list = self::lb();
                self::$list->initialize(
                    array(
                        'id'    => 'page_id',
                        'title' => 'menu_title',
                        'current' => 'is_current',
                        'create_level_css' => false,
                    ));
            }
            return self::$list;
        }

        /**
         * creates a breadcrumb menu (path to current page)
         *
         * @access public
         * @param  integer  $pid     - page_id
         * @param  array    $options - optional
         * @return string
         **/
        public static function breadcrumbMenu(array &$options = array())
        {
            self::log()->addDebug('breadcrumbMenu');

            $lb   = Base::lb();
            $menu = array();

            self::checkOptions($options);

            if(self::router()->isBackend())
            {
                $route = self::router()->getController();
                $curr  = pathinfo($route,PATHINFO_FILENAME);
                $result = self::db()->query(
                    'SELECT `trail` FROM `:prefix:backend_areas` WHERE `name`=?',
                    array(strtolower($curr))
                );
                $path = $result->fetch();
                if(isset($path['trail']) && strlen($path['trail']))
                {
                    $trail = explode('/',$path['trail']);
                    $be_menu = \CAT\Backend::getMainMenu();
                    foreach(array_values($trail) as $page)
                    {
                        $item = \CAT\Helper\HArray::extract($be_menu,'id',$page);
                        $key  = key($item);
                        // 'flatten' the array to avoid nested list
                        $item[$key]['parent'] = 0;
                        // add icon
                        if(isset($options['iconclass']) && $options['iconclass']) {
                            $item[$key]['title'] = '<i class="'.$options['iconclass'].$item[$key]['name'].'"></i> '.$item[$key]['title'];
                        }
                        $menu[] = $item[$key];
                    }

                    if(isset($options['add_home']) && $options['add_home']) {
                        $item = \CAT\Helper\HArray::extract($be_menu,'name','dashboard');
                        $key  = key($item);
                        // 'flatten' the array to avoid nested list
                        $item[$key]['parent'] = 0;
                        array_unshift($menu,$item[$key]);
                    }

                    if(isset($options['before']) && $options['before']) {
                        array_unshift(
                            $menu,
                            array(
                                'id' => 99999999,
                                'title' => $options['before'],
                                'parent' => 0,
                                'level' => 0,
                                'href' => CAT_ADMIN_URL
                            )
                        );
                    }

                    if(isset($options['after']) && $options['after']) {
                        array_push(
                            $menu,
                            array(
                                'id' => 88888888,
                                'title' => $options['after'],
                                'parent' => 0,
                                'level' => 0
                            )
                        );
                    }

                    $lb->set('id','id');
                    $lb->set('title','title');
                }
            } else {
                $pid = NULL;
                self::checkPageId($pid);
                self::log()->debug('current page [{pid}] options [{opt}]',array('pid'=>$pid,'opt'=>print_r($options,1)));
                // get the level of the current page
                $level    = \CAT\Helper\Page::properties($pid,'level');
                // get the path
                $subpages = array_reverse(\CAT\Helper\Page::getPageTrail($pid,false,true));
                self::log()->debug('level [{level}] pages [{pages}]',array('level'=>$level,'pages'=>print_r($subpages,1)));
                // add the pages to the menu
                foreach($subpages as $id)
                {
                    $pg = \CAT\Helper\Page::properties($id);
                    $menu[] = $pg;
                }
            }

            // check if the current page should be shown
            if(!isset($options['show_current']) || !$options['show_current'])
            {
                array_pop($menu); // remove last item = current page
            }
            else
            {
                if(isset($options['link_current']) && !$options['link_current'])
                {
                    $item = array_shift($menu);
                    $item['href'] = NULL;
                    array_unshift($menu,$item);
                }
            }
            self::log()->debug('pages: '.print_r($menu,1));

            // set root id to the root parent to make the listbuilder work
            #$options['root_id'] = \CAT\Helper\Page::getRootParent($pid);
            $options['root_id'] = 0;

            // return the menu
            return $lb->buildList($menu,$options);

        }   // end function breadcrumbMenu()
        
        /**
         * creates a full menu with all visible pages (like a sitemap)
         *
         * @access public
         * @param  integer  $menu_number - default NULL means all pages
         * @param  array    $options     - optional
         * @return string
         **/
        public static function fullMenu($menu_number=NULL,array &$options = array())
        {
            self::log()->addDebug('fullMenu - menu number [{num}]',array('num'=>$menu_number));
            $pid = NULL;
            self::checkPageId($pid);
            self::checkOptions($options);
            self::log()->addDebug('current page [{pid}] options [{opt}]',array('pid'=>$pid,'opt'=>print_r($options,1)));
            $menu = $menu_number
                  ? \CAT\Helper\Page::getPagesForMenu($menu_number)
                  : \CAT\Helper\Page::getPages()
                  ;
            self::markTrail($pid,$menu);
// -----------------------------------------------------------------------------
// ----- !!!FIX ME!!! ----------------------------------------------------------
            #$options['root_id'] = \CAT\Helper\Page::getRootParent($pid);
            $options['root_id'] = 0;
// -----------------------------------------------------------------------------
            self::listbuilder(true)->set($options);
            return self::listbuilder()->buildList($menu,$options);
        }   // end function fullMenu()

        /**
         * creates a siblings menu for given page_id (pages on same level)
         *
         * the menu number is derived from the given page
         *
         * @access public
         * @param  integer  $pid     - page id
         * @param  array    $options - optional
         * @return string
         **/
        public static function siblingsMenu($pid=NULL,array &$options = array())
        {
            self::log()->addDebug('siblingsMenu');
            $pid = NULL;
            self::checkPageId($pid);
            self::checkOptions($options);
            self::log()->addDebug(sprintf('create a siblingsmenu for page with id [%s]',$pid));
            self::log()->addDebug('options:',$options);
            // get the menu number
            $menu_no  = \CAT\Helper\Page::properties($pid,'menu');
            // get the level of the current/given page
            $level    = \CAT\Helper\Page::properties($pid,'level');
            // pages
            $menu     = \CAT\Helper\Page::getPagesForLevel($level,$menu_no);
            self::log()->addDebug('pages:',$menu);
            // set root id to the parent page to make the listbuilder work
            $options['root_id'] = \CAT\Helper\Page::properties($pid,'parent');
            // return the menu
            return self::listbuilder(true)->buildList($menu,$options);
        }   // end function siblingsMenu()
        
        /**
         * creates a sub menu for given page_id (children of that page)
         *
         * @access public
         * @param  integer  $pid     - page id
         * @param  array    $options - optional
         * @return string
         **/
        public static function subMenu($pid=NULL,array &$options = array())
        {
            self::log()->addDebug('subMenu');
            $pid = NULL;
            self::checkPageId($pid);
            self::checkOptions($options);
            // get the pages
            $pages = \CAT\Helper\Page::getSubPages($pid);
            // add current page to menu
            $menu  = array(\CAT\Helper\Page::properties($pid));
            // we need a fresh copy here...
            $lb    = self::listbuilder(true);
            if(isset($options['levels']))
            {
                $maxlevel = $menu[0]['level'] + $options['levels'];
                if(!$maxlevel) $maxlevel = 1;
                $lb->set('maxlevel',$maxlevel);
            }
            // add the pages
            foreach($pages as $sid)
                $menu[] = \CAT\Helper\Page::properties($sid);
            // set the root id to the current page
            $options['root_id'] = $pid;
            // return the menu
            return $lb->buildList($menu,$options);
        }   // end function subMenu()

        /**
         * analyzes the passed options and converts them for wbList
         * initializes wbList with the given options
         *
         * @access protected
         * @param  array     $options
         * @return void
         **/
        protected static function checkOptions(array &$options = array())
        {
            $lbopt = array();
            while ( $opt = array_shift($options) )
            {
                if(preg_match('~^(.+?)\:$~',$opt,$m))
                {
                    $key   = $m[1];
                    $value = array_shift($options);
                    if(array_key_exists($key,self::$alias_map))
                        $key = self::$alias_map[$key];
                    $lbopt[str_replace('-','_',$key)] = $value;
                    continue;
                }
            }
            self::lb()->set($lbopt);
            $options = $lbopt;
        }   // end function checkOptions()

        /**
         * makes sure that we have a valid page id; the visibility does not
         * matter here
         *
         * @access protected
         * @param  integer   $id (reference!)
         * @return void
         **/
        protected static function checkPageId(&$pid=NULL)
        {
            if($pid===NULL) $pid = \CAT\Page::getID();
            if($pid===0)    $pid = \CAT\Helper\Page::getRootParent($page_id);
        }   // end function checkPageId()

        /**
         * mark pages in trail
         *
         * @access protected
         * @return
         **/
        protected static function markTrail($pid, &$menu)
        {
            $trailpages = array_reverse(\CAT\Helper\Page::getPageTrail($pid,false,true));
            foreach(array_values($trailpages) as $id)
            {
                foreach($menu as $i => $item)
                {
                    if($item['page_id']==$id)
                    {
                        $menu[$i]['is_in_trail'] = true;
                        continue;
                    }
                }
            }
        }   // end function markTrail()
        
    }
}
