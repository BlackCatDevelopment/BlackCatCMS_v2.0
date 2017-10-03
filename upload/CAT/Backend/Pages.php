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

if (!class_exists('CAT_Backend_Pages'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Pages extends CAT_Object
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
            CAT_Helper_Page::setTitle('BlackCat CMS Backend / Pages');

            // addable addons
            $addable  = CAT_Helper_Addons::getAddons('page','name',false);
            $select   = array();
            foreach($addable as $item)
                $select[$item['addon_id']] = $item['name'];

            // pages list (parent dropdown)
            $pages = CAT_Helper_Page::getPages(true);

            $pages_list = self::lb()->buildRecursion($pages);
            $pages = self::lb()->buildSelect($pages,array('options_only'=>true));

            // now, let's load the form(s)
            $form = CAT_Backend::initForm();
            $form->loadFile('pages.forms.php',__dir__.'/forms');
            $form->setForm('be_page_add');

            // addable modules; pre-select WYSIWYG
            $select = array(0=>self::lang()->t('[none]')) + $select;
            $form->getElement('type')->setAttr('options',$select);
            $wysiwyg = CAT_Helper_Addons::getDetails('WYSIWYG','addon_id');
            if(strlen($wysiwyg) && is_numeric($wysiwyg)) {
                $form->getElement('type')->setAttr('selected',$wysiwyg);
            }

            // parent pages
            $pages = array(0=>self::lang()->t('[none]')) + $pages;

            $form->getElement('parent')->setAttr('options',$pages);

            CAT_Backend::print_header();
            self::tpl()->output(
                'backend_pages',
                array(
                    'add_page_form' => self::form()->getForm(),
                    'pages'         => $pages_list,
                )
            );
            CAT_Backend::print_footer();
        }   // end function index()
    }
}

