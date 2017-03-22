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
        @include dirname(__FILE__) . '/../Object.php';
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
         *
         * @access public
         * @return
         **/
        public static function add()
        {
            $pageID = self::getPageID();
            self::checkPerm($pageID,'pages_section_add');
            $addon  = CAT_Helper_Validate::sanitizePost('addon','numeric');
            if(!CAT_Helper_Addons::exists($addon))
                self::printFatalError('Invalid data!');
            else
                $module = CAT_Helper_Addons::getAddonDetails($addon,'directory');
            $blockID = CAT_Helper_Validate::sanitizePost('block','numeric',1);
            $result  = CAT_Sections::addSection($pageID,$module,$blockID);
            if(self::asJSON())
            {
                echo CAT_Helper_JSON::printResult($result,($result?'Success':'Failed'));
                return;
            }
        }   // end function add()
        
        /**
         * delete a section
         *
         * @access public
         * @return
         **/
        public static function delete()
        {
            $pageID = self::getPageID();
            self::checkPerm($pageID,'pages_section_delete');
            $sectionID = self::getSectionID();
            if(!CAT_Sections::exists($sectionID))
                CAT_Object::printFatalError('Invalid data!');
            $result = CAT_Sections::deleteSection($sectionID);
            if(self::asJSON())
            {
                echo CAT_Helper_JSON::printResult($result, '');
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
            self::checkPerm($pageID,NULL);
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
                    echo CAT_Helper_JSON::printResult(true, '');
                    return;
                }
            }
        }   // end function order()

        /**
         *
         * @access public
         * @return
         **/
        public static function publish()
        {
            $pageID = self::getPageID();
            self::checkPerm($pageID,'pages_section_publishing');
            $sectionID = self::getSectionID();
            if(!CAT_Sections::exists($sectionID))
                CAT_Object::printFatalError('Invalid data! (Section does not exist)');
            $data = array();
            foreach(array_values(array('publ_start','publ_end','publ_by_time_start','publ_by_time_end')) as $key)
            {
                $value = CAT_Helper_Validate::sanitizePost($key,'string');
                if($value) { // not empty or 0
                    try {
                        $d = new DateTime($value);
                        $data[$key] = $d->getTimestamp();
                    } catch ( \Exception $e ) {
                        echo "invalid time format $value - ", $e->getMessage(), "-<br />";
                    }
                } else {
                    $data[$key] = 0;
                }
            }

            if(count($data))
            {
                $result = CAT_Sections::updateSection($sectionID, $data);
                if(self::asJSON())
                {
                    echo CAT_Helper_JSON::printData($data);
                    return;
                }
            }

        }   // end function publish()

        /**
         * delete a section
         *
         * @access public
         * @return
         **/
        public static function recover()
        {
            $pageID = self::getPageID();
            self::checkPerm($pageID,'pages_section_recover');
            $sectionID = self::router()->getParam();
            if(!CAT_Sections::exists($sectionID))
                CAT_Object::printFatalError('Invalid data!');
            $result = CAT_Sections::recoverSection($sectionID);
            if(self::asJSON())
            {
                echo CAT_Helper_JSON::printResult($result, '');
                return;
            }
        }   // end function recover()

        /**
         *
         * @access public
         * @return
         **/
        public static function save()
        {
            $sectionID = self::getSectionID();
            if(!CAT_Sections::exists($sectionID))
                CAT_Object::printFatalError('Invalid data! (Section does not exist)');
            // get section details
            $section = CAT_Sections::getSection($sectionID,true);
            // get page ID
            $pageID  = $section['page_id'];
            self::checkPerm($pageID,null);
            // special case
            if($section['module']=='wysiwyg')
            {
                CAT_Addon_WYSIWYG::initialize();
                $result = CAT_Addon_WYSIWYG::save($sectionID);
            }
            else
            {
            }

            if(self::asJSON())
            {
                echo CAT_Helper_JSON::printResult($result, '');
                return;
            }
        }   // end function save()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function checkPerm($pageID,$perm)
        {
            $result = true;
            // the user needs to have the global pages_edit permission plus
            // permissions for the current page
            if(!self::user()->hasPerm('pages_edit') || !self::user()->hasPagePerm($pageID,'pages_edit'))
            {
                self::log()->addWarning(sprintf(
                    'User [%s] requested edit permissions for page [%s]',
                    self::user()->get('display_name'), $pageID
                ));
                $result = false;
            }
            if($perm && !self::user()->hasPerm($perm))
            {
                self::log()->addWarning(sprintf(
                    'User [%s] requested [%s] permissions for page [%s]',
                    self::user()->get('display_name'),$perm,$pageID
                ));
                $result = false;
            }
            if(!$result)
            {
                self::printFatalError('You are not allowed for the requested action!');
            }
        }   // end function checkPerm()
        
        /**
         * try to retrive the page id
         *     + from _POST
         *     + from _GET
         *     + from route
         *     + by section id
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

            if(!$pageID)
                $pageID = CAT_Sections::getPageForSection(self::getSectionID());

            if(!$pageID || !is_numeric($pageID) || !CAT_Helper_Page::exists($pageID))
                CAT_Object::printFatalError('Invalid data');

            return $pageID;
        }   // end function getPageID()

        /**
         *
         * @access protected
         * @return
         **/
        protected static function getSectionID()
        {
            $self    = self::getInstance();
            $sectID  = CAT_Helper_Validate::sanitizePost('section_id','numeric',NULL);

            if(!$sectID)
                $sectID  = CAT_Helper_Validate::sanitizeGet('section_id','numeric',NULL);

            if(!$sectID)
                $sectID = $self->router()->getParam(-1);

            if(!$sectID || !is_numeric($sectID) || !CAT_Sections::exists($sectID))
                CAT_Object::printFatalError('Invalid data');

            return $sectID;
        }   // end function getSectionID()

    } // class CAT_Backend_Section

} // if class_exists()