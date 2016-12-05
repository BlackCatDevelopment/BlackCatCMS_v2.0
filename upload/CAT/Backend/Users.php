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

if (!class_exists('CAT_Backend_Users'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Users extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $instance = NULL;

        /**
         * Singleton
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * get the list of users that are members of the given group
         *
         * @access public
         * @return
         **/
        public static function bygroup()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('users_membership'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id   = $self->router()->getParam();
            $data = CAT_Groups::getInstance()->getMembers($id);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($data,true);
                return;
            }
        }   // end function bygroup()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function delete()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('groups_delete'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id   = $self->router()->getParam();
            if(CAT_Helper_Users::deleteUser($id)!==true)
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
            $data  = CAT_Helper_Users::getUsers();
            if(count($data))
            {
                foreach($data as $i => $user)
                {
                    $data[$i]['groups'] = CAT_Helper_Users::getUserGroups($user['user_id']);
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

        /**
         *
         * @access public
         * @return
         **/
        public static function notingroup()
        {
            $self = self::getInstance();
            if(!$self->user()->hasPerm('users_membership'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id    = $self->router()->getParam();
            $users = CAT_Helper_Users::getUsers(array('group_id'=>$id,'not_in_group'=>true));
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($users,true);
                return;
            }
        }   // end function notingroup()

        /**
         *
         * @access public
         * @return
         **/
        public static function tfa()
        {
            $self  = self::getInstance();
            if(!$self->user()->hasPerm('users_edit'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $id   = $self->router()->getParam();
            $user = new CAT_User($id);
            $tfa  = $user->get('tfa_enabled');
            $new  = ( $tfa == 'Y' ? 'N' : 'Y' );
            $self->db()->query(
                'UPDATE `:prefix:rbac_users` SET `tfa_enabled`=? WHERE `user_id`=?',
                array($new,$id)
            );
            if($self->db()->isError())
            {
                echo CAT_Object::json_error('Unable to save');
            }
            else
            {
                echo CAT_Object::json_success('Success');
            }
        }   // end function tfa()
        

    } // class CAT_Helper_Users

} // if class_exists()