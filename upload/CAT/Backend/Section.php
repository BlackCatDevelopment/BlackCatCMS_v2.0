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

if (!class_exists('CAT_Backend_Section'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Section extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;

        /**
         * create an instance (singleton)
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
         * delete a section
         *
         * @access public
         * @return
         **/
        public static function delete()
        {
            if(!self::user()->hasPerm('pages_section_delete'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $sectionID = self::router()->getParam();
            if(!CAT_Sections::exists($sectionID))
                CAT_Object::printFatalError('Invalid data!');
            $result = CAT_Sections::deleteSection($sectionID);
            if(self::asJSON())
            {
                echo CAT_Object::json_result($result, '');
                return;
            }
        }   // end function delete()

        /**
         *
         * @access public
         * @return
         **/
        public static function order()
        {
            $pageID  = self::getPageID();
            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_edit') || !self::user()->hasPagePerm($pageID,'pages_edit'))
                CAT_Object::printFatalError('You are not allowed for the requested action!');
            $order = CAT_Helper_Validate::sanitizePost('order');
            if(is_array($order) && count($order))
            {
                foreach($order as $i => $id)
                {
                    if(!CAT_Sections::exists($id)) continue;
                    $i++;
                    self::db()->query(
                        'UPDATE `:prefix:pages_sections` SET `position`=? WHERE `section_id`=?',
                        array($i,$id)
                    );
                }
                if(self::asJSON())
                {
                    echo CAT_Object::json_result(true, '');
                    return;
                }
            }
        }   // end function order()

        /**
         * delete a section
         *
         * @access public
         * @return
         **/
        public static function recover()
        {
            if(!self::user()->hasPerm('pages_section_recover'))
                CAT_Helper_JSON::printError('You are not allowed for the requested action!');
            $sectionID = self::router()->getParam();
            if(!CAT_Sections::exists($sectionID))
                CAT_Object::printFatalError('Invalid data!');
            $result = CAT_Sections::recoverSection($sectionID);
            if(self::asJSON())
            {
                echo CAT_Object::json_result($result, '');
                return;
            }
        }   // end function recover()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function getPageID()
        {
            $self    = self::getInstance();
            $pageID  = CAT_Helper_Validate::sanitizePost('page_id','numeric',NULL);

            if(!$pageID)
                $pageID  = CAT_Helper_Validate::sanitizeGet('page_id','numeric',NULL);

            if(!$pageID)
                $pageID = $self->router()->getParam(-1);

            if(!$pageID || !is_numeric($pageID) || !CAT_Helper_Page::exists($pageID))
                CAT_Object::printFatalError('Invalid data');

            return $pageID;
        }   // end function getPageID()

    } // class CAT_Backend_Section

} // if class_exists()