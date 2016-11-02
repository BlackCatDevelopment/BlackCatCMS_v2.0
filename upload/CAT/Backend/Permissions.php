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

if (!class_exists('CAT_Backend_Permissions'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Permissions extends CAT_Object
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
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $self = self::getInstance();
            CAT_Backend::print_header();
            $perms = $self->perms()->getPerms();

            // render recursive list (ul)
            $l = \wblib\wbList::getInstance(array(
                '__id_key'       => 'perm_id',
                '__parent_key'   => 'requires',
                'top_list_open'  => '<ol id="%%id%%" class="%%class%%">',
                'top_list_close' => '</ol>',
                'list_open'      => '<ol id="%%id%%" class="%%class%%">',
                'list_close'     => '</ol>',

            ));

            // sort permissions by parents (if the template renders the list)
            $lb = CAT_Helper_ListBuilder::getInstance()->config(array(
                '__id_key'     => 'perm_id',
                '__parent_key' => 'requires',
            ));

            // pass both variants to the template
            $tpl_data = array(
                'permissions'     => $lb->sort($perms,0)
            );

            $self->tpl()->output('backend_permissions', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()
        
        /**
         * returns the permissions for a role; the role_id is retrieved from
         * the route
         *
         * @access public
         * @return
         **/
        public static function byrole()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('roles_perms'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            
            $id    = $self->router()->getParam();
            $perms = CAT_Permissions::getInstance()->getPerms($id);
            $perms = CAT_Helper_Array::ArraySort($perms,'area','asc',true,true);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($perms,true);
                return;
            }
        }   // end function byrole()
        
        /**
         * list permissions
         **/
        public static function list()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('permissions_list'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            # get the permissions
            $perms = CAT_Permissions::getInstance()->getPerms();
            # recursive
            $rec   = $self->router()->getParam();
            if($rec) {
                $lb = CAT_Helper_ListBuilder::getInstance()->config(array(
                    '__id_key'     => 'perm_id',
                    '__parent_key' => 'requires',
                ));
                $perms = $lb->buildRecursion($perms);
            }

            if(self::asJSON())
            {
                # sort by parents
                #$perms = CAT_Helper_ListBuilder::buildRecursion($perms);
                echo header('Content-Type: application/json');
                echo json_encode($perms,true);
                return;
            }
            return $perms;
        }

    } // class CAT_Helper_Permissions

} // if class_exists()