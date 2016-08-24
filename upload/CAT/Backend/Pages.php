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
 */

if (!class_exists('CAT_Backend_Pages'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Pages extends CAT_Object
    {
        protected static $instance = NULL;

        /**
         *
         * @access public
         * @return
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
            $self  = self::getInstance();
            $pages = self::list();

            $tpl_data = array(
                'pages' => CAT_Helper_ListBuilder::sort($pages,0),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_pages', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function Settings()

        public static function list()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('pages_list'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            # get the page tree
            $pages = CAT_Helper_Page::getPages(true);
            if(self::asJSON())
            {
                # sort by parents
                $pages = CAT_Helper_ListBuilder::buildRecursion($pages);
                echo header('Content-Type: application/json');
                echo json_encode($pages,true);
                return;
            }
            return $pages;
        }

    } // class CAT_Helper_Settings

} // if class_exists()