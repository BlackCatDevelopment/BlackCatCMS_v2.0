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

if (!class_exists('CAT_Backend_Page'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Page extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
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
         * returns the pages list, sorted by parent -> children -> position
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            // note: the access rights for index() are checked by the Backend
            // class, so there's no need to do it here
            $self  = self::getInstance();
            $pages = self::list();
            // sort pages by children
            $tpl_data = array(
                'pages' => CAT_Helper_ListBuilder::sort($pages,0),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_pages', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function edit()
        {
            global $parser, $section_id, $page_id;

            $self = self::getInstance();

            // the user needs to have the global pages_edit permission
            if(!$self->user()->hasPerm('pages_edit'))
                CAT_Object::json_error('You are not allowed for the requested action!');

            $current_template = CAT_Helper_Page::getPageTemplate($page_id);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Rechte auf die Seite an sich pruefen
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $page_id    = $self->router()->getParam();
            if(!$page_id || !is_numeric($page_id) || !CAT_Helper_Page::exists($page_id))
            {
                CAT_Object::printFatalError('Invalid data');
            }

            $page       = CAT_Page::getInstance($page_id);
            $sections   = $page->getSections();
            $blockcount = 0;
            $tpl_data   = array(
                'blocks' => array()
            );

            foreach ($sections as $section)
            {
                $module     = $section['module'];
                $section_id = $section['section_id'];
                // silently skip modules the user does not have access to
                if($self->user()->hasModulePerm($section['module']))
                {
                    if(file_exists(CAT_PATH.'/modules/'.$module.'/modify.php'))
                    {
                        ob_start();
                            require(CAT_PATH.'/modules/'.$module.'/modify.php');
                            $tpl_data['blocks'][] = array(
                                'meta'    => array_merge(
                                    $section,
                                    array('blockname'=>$parser->get_template_block_name($current_template, $section['block']))
                                ),
                                'content' => ob_get_contents(),
                            );
                        ob_clean(); // allow multiple buffering for csrf-magic
                    }
                    $blockcount++;
                }
            }
            CAT_Backend::print_header();
            $self->tpl()->output('backend_pages_modify', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function edit()

        /**
         *
         *
         *
         *
         **/
        public static function list($recursive=false)
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('pages_list'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            # get the page tree
            $pages = CAT_Helper_Page::getPages(true);
            if($recursive)
                $pages = CAT_Helper_ListBuilder::buildRecursion($pages);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($pages,true);
                return;
            }
            return $pages;
        }   // end function list()

        /**
         *
         * @access public
         * @return
         **/
        public static function visibility()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('pages_edit'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $params  = $self->router()->getParams();
            $page_id = $params[0];
            $newval  = $params[1];
            if(!is_numeric($page_id)) {
                CAT_Object::json_error('Invalid value');
            }
            if(!in_array($newval,array('public','private','hidden','none','deleted','registered')))
            {
                CAT_Object::json_error('Invalid value');
            }
            $self->db()->query(
                'UPDATE `:prefix:pages` SET `visibility`=? WHERE `page_id`=?',
                array($newval,$page_id)
            );
            echo CAT_Object::json_result(
                $self->db()->isError(),
                '',
                true
            );
        }   // end function visibility()
        

    } // class CAT_Backend_Page

} // if class_exists()