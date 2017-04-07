<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2017 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

if (!class_exists('CAT_Backend_Roles'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Roles extends CAT_Object
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
    }
}