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
        protected        $_config   = array();
        // array to hold the user data
        protected        $user      = array();
        // array to hold the user roles
        protected        $roles     = array();
        // array to hold the permissions
        protected        $perms     = array();
        // array to hold the user groups
        protected        $groups    = array();
        // array to hold the list of pages a user owns
        protected        $pages     = array();
        // last user error
        protected        $lasterror = NULL;
        // cache already loaded users
        protected static $users     = array();
        // log level
        protected static $loglevel  = \Monolog\Logger::EMERGENCY;

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
            $this->log()->debug(sprintf('id: [%d]',$id));
            if($id) {
                $this->initUser($id); // load user
            }
        }   // end function __construct()

        public static function getInstance($id=NULL)
        {
            return new self($id);
        }   // end function getInstance()


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
         *
         * @access public
         * @return
         **/
        public function getError()
        {
            return $this->lasterror;
        }   // end function getError()

        /**
         *
         * @access public
         * @return
         **/
        public function getGroups()
        {
            return $this->groups;
        }   // end function getGroups()

        /**
         *
         * @access public
         * @return
         **/
        public function getPerms()
        {
            return $this->perms;
        }   // end function getPerms()

        /**
         *
         * @access public
         * @return
         **/
        public function setError($msg)
        {
            $this->log()->debug($msg);
            $this->lasterror = $msg;
        }   // end function setError()

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
         * @return boolean
         **/
        public function authenticate()
        {
            $this->reset();

            $field = CAT_Helper_Validate::sanitizePost('username_fieldname');
            $user  = htmlspecialchars(CAT_Helper_Validate::sanitizePost($field),ENT_QUOTES);
            $name  = preg_match('/[\;\=\&\|\<\> ]/',$user) ? '' : $user;

            $field = CAT_Helper_Validate::sanitizePost('password_fieldname');
            $pass  = sha1(CAT_Helper_Validate::sanitizePost($field));

            $this->log()->debug(sprintf('Trying to authenticate user [%s]',$name));

            $get_user = CAT_Helper_DB::getInstance()->query(
                'SELECT `user_id` FROM `:prefix:rbac_users` WHERE `username`=:name AND `password`=:pw AND `active`=1',
                array('name'=>$name,'pw'=>$pass)
            );

            if($get_user->rowCount() != 0) // user found and password ok
            {
    			$id = $get_user->fetch(\PDO::FETCH_ASSOC);
                $this->initUser($id['user_id']);
                // 2-Step Auth
                if(ENABLE_TFA && $this->get('tfa_enabled') == 'Y')
                {
                    $field = CAT_Helper_Validate::sanitizePost('token_fieldname');
                    $token = htmlspecialchars(CAT_Helper_Validate::sanitizePost($field),ENT_QUOTES);
                    $tfa   = new \RobThree\Auth\TwoFactorAuth(WEBSITE_TITLE);
                    if($tfa->verifyCode($this->get('tfa_secret'),$token) !== true)
                    {
                        $this->reset();
                        $this->setError('Two step authentication failed!');
                        return false;
                    }
                }
                return true;
            }
            else
            {
                $this->setError('No such user, user not active, or invalid password!');
            }
            return false;
        }   // end function authenticate()

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
         * create a new secret for a user
         *
         * @access public
         * @return binary  QRCode image
         **/
        public function createSecret()
        {
            $ignore = new CAT_Helper_QRCode(); // just to make sure the helper is loaded
            $mp = new CAT_Helper_QRCodeProvider(); // needed for image creation
            $tfa = new \RobThree\Auth\TwoFactorAuth(WEBSITE_TITLE, 6, 30, 'sha1', $mp);
            $secret = $tfa->createSecret(); // generate a new secret
            $this->db()->query(
                'UPDATE `:prefix:rbac_users` SET `secret`=? WHERE `username`=?',
                array($secret,$this->get('username'))
            );
            return $tfa->getQRCodeImageAsDataUri($this->get('display_name'), $secret);
        }   // end function createSecret()

        /**
         * checks if the user is member of the given group
         *
         * @access public
         * @param  integer  $group
         * @return boolean
         * @return
         **/
        public function hasGroup($group)
        {
            foreach(array_values($this->groups) as $gr)
            {
                if($gr['group_id'] == $group)
                {
                    return true;
                }
            }
            return false;
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
            else
                // member of admin group
                if($this->hasGroup(1))
                    return true;
                else
                    return false;
        }   // end function is_root()

        /**
         *
         * @access public
         * @return
         **/
        public function isOwner($page_id)
        {
            if(isset($this->user) && $this->is_root())
                return true;
            if(count($this->pages) && in_array($page_id,$this->pages))
                return true;
            return false;
        }   // end function isOwner()
        

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
            $this->pages  = array();
        }   // end function reset()

        /**
         *
         * @access protected
         * @return
         **/
        protected function initUser($id)
        {
            $this->log()->debug(sprintf('init user with id: [%d]',$id));
            // read user from DB
            $get_user = CAT_Helper_DB::getInstance()->query(
                'SELECT `user_id`, `username`, `display_name`, `email`, `language`, `home_folder`, `tfa_enabled`, `tfa_secret` '.
                'FROM `:prefix:rbac_users` WHERE user_id=:id',
                array('id'=>$id)
            );
            // load data into object
    		if($get_user->rowCount() != 0)
            {
    			$this->user = $get_user->fetch(\PDO::FETCH_ASSOC);
                $this->log()->debug('user data:'.print_r($this->user,1));
                $this->initRoles();
                $this->log()->debug('user roles:'.print_r($this->roles,1));
                $this->initGroups();
                $this->log()->debug('user groups:'.print_r($this->groups,1));
                $this->initPerms();
                $this->log()->debug('user permissions:'.print_r($this->perms,1));
                $this->initPages();

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
            $this->roles = CAT_Roles::getInstance()->getRoles(
                array('for'=>'user','user'=>$this->user['user_id'])
            );
        }   // end function initRoles()

        /**
         *
         * @access protected
         * @return
         **/
        protected function initPerms()
        {
            if($this->is_root())
            {
                $sth = CAT_Helper_DB::getInstance()->query(
                    'SELECT * FROM `:prefix:rbac_permissions`'
                );
                $perms = $sth->fetchAll(\PDO::FETCH_ASSOC);
                foreach(array_values($perms) as $perm)
                {
                    $this->perms[$perm['title']] = true;
                }
            }
            else
            {
                if(is_array($this->roles))
                {
                    foreach($this->roles as $role)
                    {
                        $q = 'SELECT * FROM `:prefix:rbac_rolepermissions` AS t1
                        JOIN `:prefix:rbac_permissions` AS t2
                        ON `t1`.`perm_id`=`t2`.`perm_id`
                        WHERE `t1`.`role_id`=:id';
                        $opt = array('id'=>$role['role_id']);
                    }
                    if($q)
                    {
                        $sth = CAT_Helper_DB::getInstance()->query($q, $opt);
                        $perms = $sth->fetchAll(\PDO::FETCH_ASSOC);
                        foreach(array_values($this->roles) as $role)
                        {
                            foreach(array_values($perms) as $perm)
                            {
                                $this->perms[$perm['title']] = $role['role_id'];
                            }
                        }
                    }
                }
            }
        }   // end function initPerms()

        /**
         * get the list of pages a user owns
         *
         * @access protected
         * @return
         **/
        protected function initPages()
        {
            if(!$this->is_root())
            {
                $q = 'SELECT * FROM `:prefix:rbac_pageowner` AS t1
                WHERE `t1`.`owner_id`=?';
                $sth = CAT_Helper_DB::getInstance()->query($q,array($this->user['user_id']));
                $pages = $sth->fetchAll(\PDO::FETCH_ASSOC);
                foreach(array_values($pages) as $pg)
                {
                    $this->pages[] = $pg['page_id'];
                }
            }
        }   // end function initPages()
        
        

        /**
         *
         * @access protected
         * @return
         **/
        protected function initGroups()
        {
            $this->groups = CAT_Users::getUserGroups($this->user['user_id']);
        }   // end function initGroups()

    } // class CAT_User

} // if class_exists()