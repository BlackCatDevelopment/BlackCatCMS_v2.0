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

if (!class_exists('CAT_Backend_Groups'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Groups extends CAT_Object
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
            if(!CAT_Object::user()->hasPerm('groups_add'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val   = CAT_Helper_Validate::getInstance();
            $name  = $val->sanitizePost('group_name');
            $desc  = $val->sanitizePost('group_description');
            if(CAT_Groups::getInstance()->exists($name))
                CAT_Object::json_error('A group with the same name already exists!');
            CAT_Groups::getInstance()->addGroup($name,$desc);
        }

        public static function delete()
        {
            if(!CAT_Object::user()->hasPerm('groups_delete'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $val   = CAT_Helper_Validate::getInstance();
            $id    = $val->sanitizePost('id');
            if(!CAT_Groups::getInstance()->exists($id))
                CAT_Object::json_error('No such group!');
            $group = CAT_Groups::getInstance()->getGroup($id);
            if($group['builtin']=='Y')
                CAT_Object::json_error('Built-in elements cannot be removed!');
            $res   = CAT_Groups::getInstance()->removeGroup($id);
            CAT_Object::json_result($res,($res?'':'Failed!'),($res?true:false));
        }

        public static function edit()
        {
// ----- TODO: check permissions -----
            $val = CAT_Helper_Validate::getInstance();
            $field = $val->sanitizePost('name');
            $id    = $val->sanitizePost('pk');
            $value = $val->sanitizePost('value');
            CAT_Groups::getInstance()->set($field,$value,$id);
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
                'groups' => CAT_Groups::getInstance()->getGroups(),
                'perms'  => CAT_User::getInstance()->getPerms(),
            );
            foreach($tpl_data['groups'] as $i => $g)
            {
                $members = CAT_Groups::getInstance()->getMembers($g['group_id']);
                $roles   = CAT_Roles::getInstance()->getRoles(array('for'=>'group','id'=>$g['group_id']));
                $tpl_data['groups'][$i]['member_count'] = count($members);
                $tpl_data['groups'][$i]['role_count']   = count($roles);
            }
            CAT_Backend::print_header();
            $self->tpl()->output('backend_groups', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function media()
        

    } // class CAT_Helper_Groups

} // if class_exists()