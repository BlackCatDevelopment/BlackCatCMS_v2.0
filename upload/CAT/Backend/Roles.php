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
         * create role
         *
         * @access public
         * @return
         **/
        public static function create()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('roles_add'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val   = CAT_Helper_Validate::getInstance();
            $name  = $val->sanitizePost('role_name');
            $desc  = $val->sanitizePost('role_description');
            if(CAT_Roles::getInstance()->exists($name))
                CAT_Object::json_error('A role with the same name already exists!');
            $result = CAT_Roles::getInstance()->addRole($name,$desc);
            echo CAT_Object::json_result(
                $result,
                '',
                true
            );
        }   // end function create()

        /**
         * delete a role; requires the role id as route param
         *    example: /roles/delete/99
         * prints JSON result (success or error) to STDOUT
         *
         * @access public
         * @return void
         **/
        public static function delete()
        {
            $self  = self::getInstance();
            if(!$self->user()->hasPerm('roles_delete'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id    = $self->router()->getParam();
            if(!CAT_Roles::getInstance()->exists($id))
                CAT_Object::json_error('No such role!');
            $role  = CAT_Roles::getInstance()->getRole($id);
            if($role['builtin']=='Y')
                CAT_Object::json_error('Built-in elements cannot be removed!');
            $res   = CAT_Roles::getInstance()->removeRole($id);
            CAT_Object::json_result($res,($res?'':'Failed!'),($res?true:false));
        }   // end function delete()

        /**
         * edit role
         *
         * @access public
         * @return
         **/
        public static function edit()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('roles_edit'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val = CAT_Helper_Validate::getInstance();
            $field = $val->sanitizePost('name');
            $id    = $val->sanitizePost('pk');
            $value = $val->sanitizePost('value');
            CAT_Roles::getInstance()->set($field,$value,$id);
        }   // end function edit()

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $self  = self::getInstance();
            $roles = $self->roles()->getRoles();

            // counter
            foreach($roles as $i => $r)
            {
                $users  = $self->roles()->getUsers($r['role_id']);
                $groups = $self->roles()->getGroups($r['role_id']);
                $perms  = $self->perms()->getPerms($r['role_id']);
                $roles[$i]['user_count']  = ( is_array($users)  ? count($users)  : 0 );
                $roles[$i]['group_count'] = ( is_array($groups) ? count($groups) : 0 );
                $roles[$i]['perm_count']  = ( is_array($perms)  ? count($perms)  : 0 );
            }

            $tpl_data = array(
                'roles' => $roles,
                'perms' => $self->perms()->getPerms(),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_roles', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function saveperms()
        {
            $self  = self::getInstance();
            if(!$self->user()->hasPerm('roles_perms'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $role_id  = $self->router()->getParam();
            $selected = CAT_Helper_Validate::sanitizePost('perms');
            // get old data
            $perms    = $self->perms()->getPerms($role_id);
            // extract the ids
            $ids      = array_column($perms, 'perm_id');
            // save new
            foreach(array_values($selected) as $perm_id)
            {
                // added
                if(!in_array($perm_id,$ids))
                {
                    $self->db()->query(
                        'INSERT INTO `:prefix:rbac_rolepermissions` VALUES ( ?, ?, ? )',
                        array($role_id, $perm_id, time())
                    );
                }
            }
            foreach(array_values($ids) as $perm_id)
            {
                // removed
                if(!in_array($perm_id,$selected))
                {
                    $self->db()->query(
                        'DELETE FROM `:prefix:rbac_rolepermissions` WHERE `role_id`=? AND `perm_id`=?',
                        array($role_id,$perm_id)
                    );
                }
            }
            echo CAT_Object::json_success('Success');
        }   // end function saveperms()
        


    } // class CAT_Helper_Roles

} // if class_exists()