<?php

/**
 *    @author          Black Cat Development
 *    @copyright       2016, Black Cat Development
 *    @link            http://blackcat-cms.org
 *    @license         http://www.gnu.org/licenses/gpl.html
 *    @category        CAT_Core
 *    @package         CAT_Core
 **/

if (!class_exists('CAT_Roles'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include __DIR__ . '/Object.php';
    }

    class CAT_Roles extends CAT_Object
    {
        protected        $_config  = array( 'loglevel' => 7 );
        protected        $roles    = array();
        protected        $perms    = array();
        protected static $instance = NULL;

        /**
         * create a new roles object
         *
         * @access public
         * @return object
         **/
        public function __construct($id=NULL)
        {
            parent::__construct();
            $this->initRoles();
            $this->initPerms();
            foreach($this->roles as $i => $role)
            {
                $perms  = $this->getPerms($role['role_id']);
                $users  = $this->getUsers($role['role_id']);
                $groups = $this->getGroups($role['role_id']);
                $this->roles[$i]['perm_count'] = count($perms);
                $this->roles[$i]['user_count'] = count($users);
            }
        }   // end function __construct()

        /**
         * get singleton
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
        public function addRole($name,$description)
        {
            $sth = CAT_Helper_DB::getInstance()->query(
                  'INSERT INTO `:prefix:rbac_roles` ( `title`, `description` ) '
                . 'VALUES ( :name, :desc )',
                array('name'=>$name,'desc'=>$description)
            );
        }   // end function addGroup()

        /**
         *
         * @access public
         * @return
         **/
        public function exists($role_name)
        {
            if(!$this->roles) $this->initRoles();
            foreach($this->roles as $role)
            {
                if(strcasecmp($role['title'],$role_name)) return true;
            }
            return false;
        }   // end function exists()

        /**
         *
         * @access public
         * @return
         **/
        public function getGroups($id)
        {
            return $this->getRoles(array('group'=>$id));
        }   // end function getGroups()
        /**
         * returns the permissions for the given role
         *
         * @access public
         * @param  integer  $role_id
         * @return array
         **/
        public function getPerms($role_id)
        {
            return ( isset($this->perms[$role_id])
                ? $this->perms[$role_id]
                : false
            );
        }   // end function getPerms()
        
        /**
         * get roles
         * to get the roles for a specific user, pass:
         *    $opt = array('for'=>'user','id'=><user_id>)
         * to get the roles for a specific group, pass:
         *    $opt = array('for'=>'group','id'=><group_id>)
         * to get all roles, don't pass any options
         *
         * @access public
         * @param  array   $opt - optional options array
         * @return array
         **/
        public function getRoles($opt=NULL)
        {
            if(is_array($opt))
            {
                if(!isset($opt['for']) || !in_array($opt['for'],array('user','group')))
                    return false;
                $table = $opt['for'];
                $query = 'SELECT t1.`role_id`, t2.`title` '
                       . 'FROM `:prefix:rbac_%sroles` AS t1 '
                       . 'JOIN `:prefix:rbac_roles` AS t2 '
                       . 'ON t1.`role_id`=t2.`role_id` '
                       ;

                if(isset($opt['id']) && strlen($opt['id']))
                {
                    $query .= 'WHERE t1.`'.$table.'_id`=:id';
                    $query_options = array('id'=>$opt['id']);
                }
                elseif(isset($opt['group']) && strlen($opt['group']))
                {
                    $query .= 'WHERE t1.`'.$table.'_id`=:id';
                    $query_options = array('id'=>$opt['id']);
                }

                $sth = CAT_Helper_DB::getInstance()->query(
                    sprintf($query,$table),
                    $query_options
                );
                return $sth->fetchAll(\PDO::FETCH_ASSOC);
            }

            return $this->roles;
        }   // end function getRoles()

        /**
         *
         * @access public
         * @return
         **/
        public function getUsers($id)
        {
            return $this->getRoles(array('user'=>$id));
        }   // end function getUsers()

        /**
         *
         * @access public
         * @return
         **/
        public function set($field,$value,$id)
        {
            $dbh  = CAT_Helper_DB::getInstance();
            $sth  = $dbh->query(
                'UPDATE `:prefix:rbac_roles` SET `:fieldname:`=:value WHERE `role_id`=:id',
                array('fieldname'=>$field,'value'=>$value,'id'=>$id)
            );
            if(!$dbh->isError()) return true;
            else                 return false;
        }   // end function set()

        /**
         * init permissions; loads the permissions assigned to each role and
         * stores the assignments into protected 'perms' array
         *
         * @access protected
         * @return
         **/
        protected function initPerms()
        {
            if(!$this->roles) $this->initRoles();
            if(!is_array($this->roles) || !count($this->roles)) return array();

            $dbh = CAT_Helper_DB::getInstance();
            $q   = 'SELECT * FROM `:prefix:rbac_rolepermissions` AS t1
                   JOIN `:prefix:rbac_permissions` AS t2
                   ON `t1`.`perm_id`=`t2`.`perm_id`'
                 ;
            $sth   = $dbh->query($q);
            $perms = $sth->fetchAll(\PDO::FETCH_ASSOC);

            foreach(array_values($perms) as $perm)
            {
                $this->perms[$perm['role_id']][] = $perm;
            }
        }   // end function initPerms()

        /**
         * init roles; loads all defined roles from the database and stores
         * the list into protected 'roles' array
         *
         * @access protected
         * @return void
         **/
        protected function initRoles()
        {
            $dbh  = CAT_Helper_DB::getInstance();
            $sth  = $dbh->query(
                'SELECT * FROM `:prefix:rbac_roles`'
            );
            $this->roles = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function initRoles()

    } // class CAT_Roles

} // if class_exists()