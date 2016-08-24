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
        protected        $roles    = array();
        protected static $instance = NULL;
        protected static $loglevel = \Monolog\Logger::EMERGENCY;

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
         * get roles (optionally filtered by given conditions)
         *
         * to get the roles for a specific user, pass:
         *    $opt = array('for'=>'user','user_id'=><user_id>)
         * to get the roles for a specific group, pass:
         *    $opt = array('for'=>'group','group_id'=><group_id>)
         * to get the users for a given role, pass:
         *    $opt = array('for'=>'user','role_id'=><role_id>)
         *
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

                $self = self::getInstance();

                $table = $opt['for'];
                $query = 'SELECT * '
                       . 'FROM `:prefix:rbac_%sroles` AS t1 '
                       . 'JOIN `:prefix:rbac_roles` AS t2 '
                       . 'ON t1.`role_id`=t2.`role_id` '
                       ;
                $query_options = array();

                if(isset($opt['user_id']) && strlen($opt['user_id']))
                {
                    $query .= 'WHERE t1.`'.$table.'_id`=:id';
                    $query_options = array('id'=>$opt['user_id']);
                }
                if(isset($opt['group_id']) && strlen($opt['group_id']))
                {
                    $query .= 'WHERE t1.`'.$table.'_id`=:id';
                    $query_options = array('id'=>$opt['group_id']);
                }
                if(isset($opt['role_id']) && strlen($opt['role_id']))
                {
                    $query .= 'WHERE t2.`role_id`=:id';
                    $query_options = array('id'=>$opt['role_id']);
                }

                $sth = $self->db()->query(
                    sprintf($query,$table),
                    $query_options
                );
                return $sth->fetchAll(\PDO::FETCH_ASSOC);
            }

            return $this->roles;
        }   // end function getRoles()

        /**
         * get users for given role
         *
         * @access public
         * @return array
         **/
        public function getUsers($id)
        {
            return $this->getRoles(array('for'=>'user','role_id'=>$id));
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