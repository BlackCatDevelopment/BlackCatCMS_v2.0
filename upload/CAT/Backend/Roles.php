<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Backend_Roles'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Roles extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 7 );
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

        public static function create()
        {
            if(!CAT_Object::user()->hasPerm('roles_add'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val   = CAT_Helper_Validate::getInstance();
            $name  = $val->sanitizePost('role_name');
            $desc  = $val->sanitizePost('role_description');
            if(CAT_Roles::getInstance()->exists($name))
                CAT_Object::json_error('A role with the same name already exists!');
            CAT_Roles::getInstance()->addRole($name,$desc);
        }

        public static function edit()
        {
            if(!CAT_Object::user()->hasPerm('roles_edit'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val = CAT_Helper_Validate::getInstance();
            $field = $val->sanitizePost('name');
            $id    = $val->sanitizePost('pk');
            $value = $val->sanitizePost('value');
            CAT_Roles::getInstance()->set($field,$value,$id);
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $self = self::getInstance();
            $tpl_data = array(
                'roles' => CAT_Roles::getInstance()->getRoles(),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_roles', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function media()
        

    } // class CAT_Helper_Roles

} // if class_exists()