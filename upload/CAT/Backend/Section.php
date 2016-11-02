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
        public static function delete()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('pages_modify'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id   = $self->router()->getParam();
            $result = CAT_Sections::getInstance()->deleteSection($id);
            echo CAT_Object::json_result($result,'');
        }   // end function delete()

        /**
         *
         * @access public
         * @return
         **/
        public static function edit()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('pages_modify'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val    = CAT_Helper_Validate::getInstance();
            $field  = $val->sanitizePost('name');
            $id     = $val->sanitizePost('pk');
            $value  = $val->sanitizePost('value');
            $result = CAT_Sections::getInstance()->updateSection(
                $id,
                array($field=>$value)
            );
            echo CAT_Object::json_result($result,'');
        }   // end function edit()

        /**
         *
         * @access public
         * @return
         **/
        public static function publish()
        {
            $self  = self::getInstance();
            $id    = $self->router()->getParam();
            $start = CAT_Helper_Validate::sanitizePost('publ_start');
            $end   = CAT_Helper_Validate::sanitizePost('publ_end');
            $self->db()->query(
                'UPDATE `:prefix:sections` SET `publ_start`=?, `publ_end`=? WHERE `section_id`=?',
                array(($start?$start:0),($end?$end:0),$id)
            );
            if($self->db()->isError()) echo $self::json_error('error');
            else                       echo $self::json_success('ok');

        }   // end function publish()
        

    } // class CAT_Helper_Settings

} // if class_exists()