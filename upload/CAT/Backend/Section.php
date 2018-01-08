<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2018 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

namespace CAT\Backend;
use \CAT\Base as Base;

if (!class_exists('\CAT\Backend\Section'))
{
    class Section extends Base
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;
        protected static $debug    = false;

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
            $addon  = \CAT\Helper\Validate::sanitizePost('addon','numeric');
            if(!\CAT\Helper\Addons::exists($addon))
                self::printFatalError('Invalid data!')
                . (self::$debug ? '(CAT_Backend_Section::add())' : '');
            else
                $module = \CAT\Helper\Addons::getDetails($addon,'addon_id');
            $blockID = \CAT\Helper\Validate::sanitizePost('block','numeric',1);
            $result  = \CAT\Sections::addSection($pageID,$module,$blockID);
            if(self::asJSON())
            {
                echo \CAT\Helper\Json::printResult($result,($result?'Success':'Failed'));
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
            if(!\CAT\Sections::exists($sectionID))
                Base::printFatalError('Invalid data!')
                . (self::$debug ? '(CAT_Backend_Section::delete())' : '');
            $result = \CAT\Sections::deleteSection($sectionID);
            if(self::asJSON())
            {
                echo \CAT\Helper\Json::printResult($result, '');
                return;
            }
        }   // end function delete()

        /**
         *
         * @access public
         * @return
         **/
        public static function move()
        {
            // the user needs perms on current page...
            $pageID = self::getPageID();
            self::checkPerm($pageID,'pages_section_move');
            // ...and on target page...
            $toID = \CAT\Helper\Validate::sanitizePost('to');
            self::checkPerm($toID,'pages_section_move');
            // get the section ID
            $sectionID = self::getSectionID();
            if(!\CAT\Sections::exists($sectionID))
                Base::printFatalError('Invalid data! (Section does not exist)');
            self::db()->query(
                'UPDATE `:prefix:pages_sections` SET `page_id`=? WHERE `section_id`=?',
                array($toID,$sectionID)
            );
            if(self::asJSON())
            {
                echo \CAT\Helper\Json::printResult(
                    (self::db()->isError() ? false : true),
                    ''
                );
                return;
            }
        }   // end function move()

        /**
         *
         * @access public
         * @return
         **/
        public static function order()
        {
            $pageID  = self::getPageID();
            self::checkPerm($pageID,NULL);
            $order = \CAT\Helper\Validate::sanitizePost('order');
            if(is_array($order) && count($order))
            {
                foreach($order as $i => $id)
                {
                    if(!\CAT\Sections::exists($id)) continue;
                    $i++;
                    self::db()->query(
                        'UPDATE `:prefix:pages_sections` SET `position`=? WHERE `section_id`=?',
                        array($i,$id)
                    );
                }
                if(self::asJSON())
                {
                    echo \CAT\Helper\Json::printResult(true, '');
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
            if(!\CAT\Sections::exists($sectionID))
                Base::printFatalError('Invalid data! (Section does not exist)');
            $data = array();
            foreach(array_values(array('publ_start','publ_end','publ_by_time_start','publ_by_time_end')) as $key)
            {
                $value = \CAT\Helper\Validate::sanitizePost($key,'string');
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
                $result = \CAT\Sections::updateSection($sectionID, $data);
                if(self::asJSON())
                {
                    echo \CAT\Helper\Json::printData($data);
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
            if(!\CAT\Sections::exists($sectionID))
                Base::printFatalError('Invalid data!')
                . (self::$debug ? '(CAT_Backend_Section::recover())' : '');
            $result = \CAT\Sections::recoverSection($sectionID);
            if(self::asJSON())
            {
                echo \CAT\Helper\Json::printResult($result, '');
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
            if(!\CAT\Sections::exists($sectionID))
                Base::printFatalError('Invalid data! (Section does not exist)');
            // get section details
            $section = \CAT\Sections::getSection($sectionID,true);
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
                echo \CAT\Helper\Json::printResult($result, '');
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
            $pageID  = \CAT\Helper\Validate::sanitizePost('page_id','numeric',NULL);

            if(!$pageID)
                $pageID  = \CAT\Helper\Validate::sanitizeGet('page_id','numeric',NULL);

            if(!$pageID)
                $pageID = $self->router()->getParam(-1);

            if(!$pageID)
                $pageID = \CAT\Sections::getPageForSection(self::getSectionID());

            if(!$pageID || !is_numeric($pageID) || !\CAT\Helper\Page::exists($pageID))
                Base::printFatalError('Invalid data')
                . (self::$debug ? '(CAT_Backend_Section::getPageID())' : '');

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
            $sectID  = \CAT\Helper\Validate::sanitizePost('section_id','numeric',NULL);

            if(!$sectID)
                $sectID  = \CAT\Helper\Validate::sanitizeGet('section_id','numeric',NULL);

            if(!$sectID)
                $sectID = $self->router()->getParam(-1);

            if(!$sectID || !is_numeric($sectID) || !\CAT\Sections::exists($sectID))
                Base::printFatalError('Invalid data')
                . (self::$debug ? '(CAT_Backend_Section::getSectionID())' : '');

            return $sectID;
        }   // end function getSectionID()

    } // class Section

} // if class_exists()