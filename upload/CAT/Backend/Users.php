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

if (!class_exists('CAT_Backend_Users'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Users extends CAT_Object
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

        /**
         *
         * @access public
         * @return
         **/
        public static function bygroup()
        {
            if(!CAT_Object::user()->hasPerm('users_membership'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $self = self::getInstance();
            $id   = CAT_Backend::getRouteParams()[0];
            $data = CAT_Groups::getMembers($id);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($data,true);
                return;
            }
        }   // end function group()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function delete()
        {
            if(!CAT_Object::user()->hasPerm('groups_delete'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id   = CAT_Backend::getRouteParams()[0];
            if(CAT_Users::deleteUser($id)!==true)
            {
                echo CAT_Object::json_error('Unable to delete the user');
            }
            else
            {
                echo CAT_Object::json_success('User successfully deleted');
            }
        }   // end function delete()

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $self  = self::getInstance();
            $data  = CAT_Users::getUsers();
            if(count($data))
            {
                foreach($data as $i => $user)
                {
                    $data[$i]['groups'] = CAT_Users::getUserGroups($user['user_id']);
                }
            }
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($data,true);
                return;
            }
            $tpl_data = array(
                'users' => $data
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_users', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

    } // class CAT_Helper_Users

} // if class_exists()