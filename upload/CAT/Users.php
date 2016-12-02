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
 *   ---------------------------------------------------------------------------
 *   THIS FILE IS FOR BACKWARD COMPATIBILITY ONLY AND WILL BE REMOVED IN
 *   FUTURE VERSIONS!
 *   ---------------------------------------------------------------------------
 *
 */


if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'CAT_Users', false ) )
{
	class CAT_Users extends CAT_Object
	{
        // singleton
        private static $instance        = NULL;

        /**
         * get singleton
         **/
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * delete a user
         *
         * @access public
         * @param  integer $user_id
         * @return mixed   true on success, db error string otherwise
         **/
        public static function deleteUser($user_id)
        {
            $self = self::getInstance();
       		$self->db()->query(
                "DELETE FROM `:prefix:rbac_users` WHERE `user_id`=:id",
                array('id'=>$user_id)
            );
            return ( $self->db()->isError() ? $self->db()->getError() : true );
        }   // end function deleteUser()

        /**
         *
         * @access public
         * @return
         **/
        public static function getUsers($opt=NULL)
        {
            $self = self::getInstance();
            $q    = 'SELECT * FROM `:prefix:rbac_users` AS `t1` ';
            $p    = array();
            if(is_array($opt))
            {
                if(isset($opt['group_id']))
                {
                    $q .= 'LEFT OUTER JOIN `:prefix:rbac_usergroups` AS `t2` '
                       .  'ON `t1`.`user_id`=`t2`.`user_id` '
                       .  'WHERE ((`t2`.`group_id`'
                       .  ( isset($opt['not_in_group']) ? '!' : '' )
                       .  '=:id'
                       ;
                    $p['id'] = $opt['group_id'];
                    if(isset($opt['not_in_group']))
                    {
                        // skip users in admin group
                        $q .= ' AND `t2`.`group_id` != 1 ) OR `t2`.`group_id` IS NULL )';
                    }
                    else
                    {
                        $q .= '))';
                    }
                }
            }
            $sth  = CAT_Helper_DB::getInstance()->query($q,$p);
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function getUsers()

        /**
         *
         * @access public
         * @return
         **/
        public static function getUserGroups($id)
        {
            $q = 'SELECT * '
               . 'FROM `:prefix:rbac_users` AS t1 '
               . 'JOIN `:prefix:rbac_usergroups` AS t2 '
               . 'ON `t1`.`user_id`=`t2`.`user_id` '
               . 'JOIN `:prefix:rbac_groups` AS t3 '
               . 'ON `t2`.`group_id`=`t3`.`group_id` '
               . 'WHERE `t1`.`user_id`=:id'
               ;
            $sth = CAT_Helper_DB::getInstance()->query($q,array('id'=>$id));
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function getUserGroups()
        
        
    }
}




?>