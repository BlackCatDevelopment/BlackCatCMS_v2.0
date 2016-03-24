<?php

/**
 *    @author          Black Cat Development
 *    @copyright       2016, Black Cat Development
 *    @link            http://blackcat-cms.org
 *    @license         http://www.gnu.org/licenses/gpl.html
 *    @category        CAT_Core
 *    @package         CAT_Core
 **/

if (!class_exists('CAT_User'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include __DIR__ . '/Object.php';
    }

    class CAT_User extends CAT_Object
    {
        protected $_config = array( 'loglevel' => 8 );
        // array to hold the user data
        protected $user    = array();
        // array to hold the user roles
        protected $roles   = array();
        // array to hold the permissions
        protected $perms   = array();
        // array to hold the user groups
        protected $groups  = array();
        // cache already loaded users
        protected static $users = array();

        /**
         * create a new user object
         * @access public
         * @param  integer  $id - user id
         * @return object
         **/
        public function __construct($id=NULL)
        {
            parent::__construct();
            $this->reset(); // make sure there is no old data
            if(!$id) {      // get ID from session
                $id = CAT_Helper_Validate::getInstance()->fromSession('USER_ID','numeric');
            }
            $this->log()->logDebug(sprintf('id: [%d]',$id));
            if($id) {
                $this->initUser($id); // load user
            }
        }   // end function __construct()

        public static function getInstance()
        {
            return new self();
        }   // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function get_initial_page($user_id=NULL,$as_array=false)
        {
            return CAT_ADMIN_URL.'/start/index.php?initial=true';
        }   // end function get_initial_page()

        // =====================================================================
        //    OOP
        // =====================================================================

        /**
         * authenticate user
         *
         * @access public
         * @return
         **/
        public function authenticate()
        {
            $this->reset();

            $field = CAT_Helper_Validate::sanitizePost('username_fieldname');
            $user  = htmlspecialchars(CAT_Helper_Validate::sanitizePost($field),ENT_QUOTES);
            $name  = preg_match('/[\;\=\&\|\<\> ]/',$user) ? '' : $user;

            $field = CAT_Helper_Validate::sanitizePost('password_fieldname');
            $pass  = sha1(CAT_Helper_Validate::sanitizePost($field));

            $get_user = CAT_Helper_DB::getInstance()->query(
                'SELECT `user_id` FROM `:prefix:rbac_users` WHERE `username`=:name AND `password`=:pw AND `active`=1',
                array('name'=>$name,'pw'=>$pass)
            );
            if($get_user->rowCount() != 0)
            {
    			$id = $get_user->fetch(\PDO::FETCH_ASSOC);
                $this->initUser($id['user_id']);
                return true;
            }
            return false;
        }   // end function authenticate()

        /**
         * handle user login
         **/
        public function login()
        {
        }   // end function login()

        /**
         * handle user login
         **/
        public function logout()
        {
            // this is not really needed, but just to be really really secure...
            if(isset($_SESSION))
                foreach(array_keys($_SESSION) as $key)
                    unset($_SESSION[$key]);

            // overwrite session array
            $_SESSION = array();

            // delete session cookie if set
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            if(!isset($_POST['_cat_ajax']) && session_id() !== '') {
                @session_destroy();
            }

            // redirect to admin login
            if(!isset($_POST['_cat_ajax']))
            {
                $redirect = str_ireplace('/logout/','/login/',$_SERVER['SCRIPT_NAME']);
                die(header('Location: '.CAT_ADMIN_URL.'/login/index.php'));
            }
            else {
                header('Content-type: application/json');
                echo json_encode(array(
                    'success' => true,
                    'message' => 'ok'
                ));
            }
        }   // end function logout()

        /**
         *
         * @access public
         * @return
         **/
        public function hasGroup($group)
        {
            if(!is_array($group)) $group = array($group);
echo "CAT_user::hasGroup()<textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
print_r( $this->groups );
echo "</textarea>";
            foreach($group as $item)
            {
            }
        }   // end function hasGroup()

        /**
         * checks if the current user has the given permission
         *
         * @access public
         * @param  string  $group     - permission group
         * @param  string  $perm      - required permission
         **/
        public function hasPerm($perm)
        {
            if($this->is_root())        return true;
            if(!is_array($this->perms)) return false;
            return array_key_exists($perm,$this->perms);
        }   // end function hasPerm()

        /**
         * get user attribute; returns NULL if the given attribute is not set
         *
         * @access public
         * @param  string  $attr - attribute name
         * @return mixed   value of $attr or NULL if not set
         **/
        public function get($attr=NULL)
        {
            if(isset($this->user))
            {
                if($attr)
                {
                    if(isset($this->user[$attr]))
                    {
                        return $this->user[$attr];
                    }
                }
                return (array)$this->user;
            }
            else
            {
                return NULL;
            }
        }   // end function get()

        /**
         * Check if the user is authenticated
         *
         * @access public
         * @return boolean
         **/
        public function is_authenticated()
        {
            if(!isset($this->user) || $this->user['user_id'] == -1)
                self::getInstance();
            if(isset($this->user) && $this->user['user_id'] != -1)
                return true;
            else
                return false;
        }   // end function is_authenticated()

        /**
         * Check if current user is superuser (the one who installed the CMS)
         *
         * @access public
         * @return boolean
         **/
        public function is_root()
        {
            if(isset($this->user) && $this->user['user_id'] == 1)
                return true;
#            else
#                // member of admin group
#                if(in_array(1,self::get_groups_id()))
#                    return true;
#                else
                    return false;
        }   // end function is_root()

        /**
         * reset the user object (to guest user)
         *
         * @access public
         * @return void
         **/
        public function reset()
        {
            $this->user   = array('user_id'=>-1,'display_name'=>'unknown','username'=>'unknown');
            $this->roles  = array();
            $this->perms  = array();
            $this->groups = array();
        }   // end function reset()

        /**
         *
         * @access protected
         * @return
         **/
        protected function initUser($id)
        {
            $this->log()->logDebug(sprintf('init user with id: [%d]',$id));
            // read user from DB
            $get_user = CAT_Helper_DB::getInstance()->query(
                'SELECT `user_id`, `primary_group`, `username`, `display_name`, `email`, `language`, `home_folder` FROM `:prefix:rbac_users` WHERE user_id=:id',
                array('id'=>$id)
            );
            // load data into object
    		if($get_user->rowCount() != 0)
            {
    			$this->user = $get_user->fetch(\PDO::FETCH_ASSOC);
                $this->log()->logDebug('user data:',$this->user);
                $this->initRoles();
                $this->log()->logDebug('user roles:',$this->roles);
                $this->initGroups();
                $this->log()->logDebug('user groups:',$this->groups);
                $this->initPerms();
                $this->log()->logDebug('user permissions:',$this->perms);
                // cache
                self::$users[$id] = $this->user;
            }
        }   // end function initUser()

        /**
         * get user roles
         *
         * @access protected
         * @return void
         **/
        protected function initRoles()
        {
            $sth = CAT_Helper_DB::getInstance()->query(
                'SELECT t1.`role_id`, t2.`title`
                FROM `:prefix:rbac_userroles` AS t1
                JOIN `:prefix:rbac_roles` AS t2
                ON t1.`role_id`=t2.`role_id`
                WHERE t1.`user_id`=:id',
                array('id'=>$this->user['user_id'])
            );
            $this->roles = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function initRoles()

        /**
         *
         * @access protected
         * @return
         **/
        protected function initPerms()
        {
            if(is_array($this->roles))
            {
                $q = 'SELECT * FROM `:prefix:rbac_rolepermissions` AS t1
                JOIN `:prefix:rbac_permissions` AS t2
                ON `t1`.`perm_id`=`t2`.`perm_id`
                WHERE `t1`.`role_id`=:id';
                foreach(array_values($this->roles) as $role)
                {
                    $sth = CAT_Helper_DB::getInstance()->query(
                        $q, array('id'=>$role['role_id'])
                    );
                    $perms = $sth->fetchAll(\PDO::FETCH_ASSOC);
                    foreach(array_values($perms) as $perm)
                    {
                        $this->perms[$perm['title']] = $role['role_id'];
                    }
                }
            }
        }   // end function initPerms()
        

        /**
         *
         * @access protected
         * @return
         **/
        protected function initGroups()
        {
            $sth = CAT_Helper_DB::getInstance()->query(
                'select
                    `t1`.`user_id`,
                    `t3`.`title`,
                    `t3`.`description`
                from `cat_rbac_users` as `t1`
                join `cat_rbac_usergroups` as `t2`
                on `t1`.`user_id`=`t2`.`user_id`
                join `cat_rbac_groups` as `t3`
                on `t2`.`group_id` = `t3`.`group_id`
                WHERE `t1`.`user_id`=:id',
                array('id'=>$this->user['user_id'])
            );
            $this->groups = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function initGroups()

    } // class CAT_User

} // if class_exists()