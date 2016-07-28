<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 **/

if (!class_exists('CAT_Frontend', false))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/Object.php';
    }

    class CAT_Frontend extends CAT_Object
    {
        protected        $_config  = array();
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        private   static $instance = array();

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * dispatch frontend route
         **/
        public static function dispatch()
        {
            global $page_id;
            $self = self::getInstance();
            $self->log()->addDebug('page id [{id}]',array('id'=>$page_id));
            // get page to show
            $page_id = CAT_Helper_Page::selectPage() or die();
            // this will show the Intro- or Default-Page if no PAGE_ID is available
            $page    = CAT_Page::getInstance($page_id);
            // hand over to page handler
            $page->show();
        }
    }
}