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

namespace CAT;

use \CAT\Base as Base;

if (!class_exists('\CAT\Page', false))
{
    class Page extends Base
    {
        // ID of last instantiated page
        private   static $curr_page  = NULL;
        // singleton, but one instance per page_id!
        private   static $instances  = array();
        // loglevel
        protected static $loglevel   = \Monolog\Logger::EMERGENCY;
        //
        protected        $page_id    = NULL;

        /**
         * get instance for page with ID $page_id
         *
         * @access public
         * @param  integer $page_id
         * @return object
         **/
        public static function getInstance($page_id=NULL)
        {
            if($page_id)
            {
                self::log()->addDebug(sprintf('\CAT\Page::getInstance(%s)',$page_id));
                if(!isset(self::$instances[$page_id]))
                {
                    self::log()->addDebug('creating new instance');
                    self::$instances[$page_id] = new self($page_id);
                    self::$instances[$page_id]->page_id = $page_id;
                }
                return self::$instances[$page_id];
            }
            else
            {
                return new self(0);
            }
        }   // end function getInstance()

        /**
         * get current page
         *
         * @access public
         * @return
         **/
        public static function getID()
        {
            if(!self::$curr_page)
            {
                if(!\CAT\Backend::isBackend())
                {
                    // check if the system is in maintenance mode
                    if(\CAT\Frontend::isMaintenance())
                    {
                        $result = self::db()->query(
                            'SELECT `value` FROM `:prefix:settings` WHERE `name`="maintenance_page"'
                        );
                        $value = $result->fetch();
                        self::$curr_page = $value['value'];
                    }
                    else
                    {
                        $route = self::router()->getRoute();
                        // no route -> get default page
                        if($route == '')
                        {
                            self::$curr_page = \CAT\Helper\Page::getDefaultPage();
                        }
                        else // find page by route
                        {
                            self::$curr_page = \CAT\Helper\Page::getPageForRoute($route);
                        }
                    }
                } else {
                    return \CAT\Backend\Page::getPageID();
                }
            }
            return self::$curr_page;
        }   // end function getID()

        /**
         * get page sections for given block
         *
         * @access public
         * @param  integer $block
         * @return void (direct print to STDOUT)
         **/
        public static function getPageContent($block=1)
        {
            $page_id = self::getID();

            self::log()->addDebug(sprintf(
                'getPageContent called for block [%s], page [%s]',
                $block, $page_id
            ));

            // check if the page exists, is not marked as deleted, and has
            // some content at all
            if(
                   !$page_id                           // no page id
                || !\CAT\Helper\Page::exists($page_id)    // page does not exist
                || !\CAT\Helper\Page::isActive($page_id)  // page not active
            ) {
                return self::print404();
            }

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Maintenance page
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            // check if user is allowed to see this page
            if(!self::user()->is_root())
            {
                // global perm
                if(!self::user()->hasPagePerm($page_id,'pages_view'))
                {
                    self::printFatalError('You are not allowed to view this page!');
                }
            }

            // get active sections
            $sections = \CAT\Sections::getSections($page_id,$block,true);

            // in fact, this should never happen, als isActive() does the same
            if(!count($sections)) // no content for this block
                return false;

            $output = array();

            foreach($sections as $block => $items)
            {
                foreach($items as $section)
                {
                    // spare some typing
                    $section_id = $section['section_id'];
                    $module     = $section['module'];

                    // special case
                    if($module=='wysiwyg')
                    {
                        $output[] = \CAT\Addon\WYSIWYG::view($section_id)['content'];
                    }
                    else
                    {
                        // get the module class
                        $name    = \CAT\Helper\Addons::getDetails($module,'name');
                        $handler = NULL;
                        foreach(array_values(array(str_replace(' ','',$name),$module)) as $classname) {
                            $filename = \CAT\Helper\Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.$module.'/inc/class.'.$classname.'.php');
                            if(file_exists($filename)) {
                                 $handler = $filename;
                            }
                        }

                        if($handler)
                        {
                            self::log()->addDebug(sprintf('found class file [%s]',$handler));
                            Base::addLangFile(CAT_ENGINE_PATH.'/modules/'.$module.'/languages/');
                            self::setTemplatePaths($module);
                            include_once $handler;
                            $classname::initialize();
                            $content = $classname::view($section_id);
                        }
                        else
                        {
                            self::log()->addError(
                                sprintf('non existing module [%s] or missing handler [%s], called on page [%d], block [%d]',
                                $module,'class.'.$module.'.php',$page_id,$block)
                            );
                        }
                    }
                }
            }
            echo implode("\n", $output);
        }   // end function getPageContent()

        /**
         *
         * @access public
         * @return
         **/
        public static function print404()
        {
            if(\CAT\Registry::exists('ERR_PAGE_404'))
            {
                $err_page_id = \CAT\Registry::get('ERR_PAGE_404');
                header($_SERVER['SERVER_PROTOCOL'].' 404 Not found');
                header('Location: '.\CAT\Helper\Page::getLink($err_page_id));
            }
            else
            {
                header($_SERVER['SERVER_PROTOCOL'].' 404 Not found');
            }
            exit;
        }   // end function print404()

        /**
         * Figure out which template to use
         *
         * @access public
         * @return void   sets globals
         **/
        public function setTemplate()
        {
/*
            if(!defined('TEMPLATE'))
            {
                $prop = $this->getProperties();
                // page has it's own template
                if(isset($prop['template']) && $prop['template'] != '') {
                    if(file_exists(\CAT\PATH.'/templates/'.$prop['template'].'/index.php')) {
                        \CAT\Registry::register('TEMPLATE', $prop['template'], true);
                    } else {
                        \CAT\Registry::register('TEMPLATE', \CAT\Registry::get('DEFAULT_TEMPLATE'), true);
                    }
                // use global default
                } else {
                    \CAT\Registry::register('TEMPLATE', \CAT\Registry::get('DEFAULT_TEMPLATE'), true);
                }
            }
            $dir = '/templates/'.TEMPLATE;
            // Set the template dir (which is, in fact, the URL, but for backward
            // compatibility, we have to keep this irritating name)
            \CAT\Registry::register('TEMPLATE_DIR', CAT_URL.$dir, true);
            // This is the REAL dir
            \CAT\Registry::register('CAT_TEMPLATE_DIR', CAT_PATH.$dir, true);
*/
        }   // end function setTemplate()

        /**
         * shows the current page
         *
         * @access public
         * @return void
         **/
        public function show()
        {
            // send appropriate header
            if(\CAT\Frontend::isMaintenance() || \CAT\Registry::get('maintenance_page') == $this->page_id)
            {
                $this->log()->addDebug('Maintenance mode is enabled');
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 7200'); // in seconds
            }

            $this->setTemplate();

            // including the template; it may calls different functions
            // like page_content() etc.
            $this->log()->addDebug('including template');

            ob_start();
                require CAT_TEMPLATE_DIR.'/index.php';
                $output = ob_get_contents();
            ob_clean();

            echo $output;
        }   // end function show()

    } // end class \CAT\Page

}
