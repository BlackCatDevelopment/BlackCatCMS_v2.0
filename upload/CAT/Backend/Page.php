<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2016 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

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
                'pages' => \wblib\wbList::sort($pages,0),
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
            $self    = self::getInstance();
            $pageID  = $self->router()->getParam(-1);

            if(!$pageID || !is_numeric($pageID) || !CAT_Helper_Page::exists($pageID))
                CAT_Object::printFatalError('Invalid data');

            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!$self->user()->hasPerm('pages_edit') || !$self->user()->hasPagePerm($pageID,'pages_edit'))
                CAT_Object::printFatalError('You are not allowed for the requested action!');

            // now, let's load the form(s)
            $form = CAT_Backend::initForm();
            $form->loadFile('pages.forms.php',__dir__.'/forms');

            $curr_tpl   = CAT_Helper_Page::getPageTemplate($pageID);
            $page       = CAT_Helper_Page::properties($pageID);
            $sections   = CAT_Helper_Page::getSections($pageID);
            $languages  = array();
            $templates  = array();
            $pages      = \wblib\wbList::sort(CAT_Helper_Page::getPages(1),0);

            // to fill the several page select fields (f.e. "parent")
            $pages_select = array();
            foreach($pages as $p)
                $pages_select[$p['page_id']] = $p['menu_title'];

            // language select
            $langs      = CAT_Helper_I18n::getLanguages();
            if(is_array($langs) && count($langs))
            {
                foreach(array_values($langs) as $lang)
                {
                    $data = CAT_Helper_Addons::getAddonDetails($lang);
                    $languages[] = $data;
                }
            }

            // template select
            if(is_array(($tpls=CAT_Helper_Addons::get_addons('template','template'))))
            {
                foreach(array_values($tpls) as $tpl)
                {
                    $templates[$tpl['directory']] = $tpl['name'];
                }
            }


            $form->setForm('be_page_settings');
            $form->setData($page);

            $form->setForm('be_page_general');
            $form->getElement('parent')
                 ->setAttr('options',array_merge(
                     array('0'=>'['.$self->lang()->t('none').']'),
                     $pages_select
                   ))
                 ->setValue($page['parent'])
                 ;
            $form->getElement('template')
                 ->setAttr('options',array_merge(
                     array(''=>'System default'),
                     $templates
                   ))
                 ->setValue($curr_tpl)
                 ;

            $blockcount = 0;
            $tpl_data   = array(
                'meta'      => array(
                    'menus'     => CAT_Helper_Template::get_template_menus($curr_tpl),
                    'variants'  => CAT_Helper_Template::getVariants($curr_tpl),
                    'pages'     => \wblib\wbList::sort(CAT_Helper_Page::getPages(1),0),
                    'page'      => CAT_Helper_Page::properties($pageID),
                ),
                'blocks'    => array(),
            );

            foreach ($sections as $block => $items)
            {
                foreach($items as $section)
                {
                    $module     = $section['module'];
                    $section_id = $section['section_id'];
                    // silently skip modules the user does not have access to
                    if($self->user()->hasModulePerm($section['module']))
                    {
                        if(file_exists(CAT_ENGINE_PATH.'/modules/'.$module.'/modify.php'))
                        {
                            // catch module output
                            ob_start();
                                require(CAT_ENGINE_PATH.'/modules/'.$module.'/modify.php');
                                $tpl_data['blocks'][] = array(
                                    'meta'    => array_merge(
                                        $section,
                                        array('blockname'=>$self->tpl()->get_template_block_name($curr_tpl, $section['block']))
                                    ),
                                    'content' => ob_get_contents(),
                                );
                            ob_clean(); // allow multiple buffering for csrf-magic
                        }
                        $blockcount++;
                    }
                }
            }

            // if the user is allowed to add sections...
            if($self->user()->hasPerm('pages_add_section'))
            {
                // ...get the list of modules the user has access to
                $available = CAT_Helper_Addons::get_addons(NULL,'module','page');
                $accessible = array();
                if(is_array($available) && count($available))
                {
                    foreach($available as $i => $item)
                    {
                        if($self->user()->hasModulePerm($item['addon_id']))
                        {
                            $accessible[] = $item;
                        }
                    }
                }
                $tpl_data['addons'] = $accessible;
            }

            CAT_Backend::print_header();
            $self->tpl()->output('backend_page_modify', $tpl_data);
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
                $pages = $self->lb()->buildRecursion($pages);
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