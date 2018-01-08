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

namespace CAT\Backend;

use \CAT\Base as Base;

if (!class_exists('\CAT\Backend\Pages'))
{
    class Pages extends Base
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;

        /**
         * Singleton
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            \CAT\Helper\Page::setTitle('BlackCat CMS Backend / Pages');

            $pages      = \CAT\Helper\Page::getPages(true);
            $pages_list = self::lb()->buildRecursion($pages);

            \CAT\Backend::print_header();
            self::tpl()->output(
                'backend_pages',
                array(
                    'pages'         => $pages_list,
                )
            );
            \CAT\Backend::print_footer();
        }   // end function index()
    }
}

