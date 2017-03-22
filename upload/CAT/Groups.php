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


if (!class_exists('CAT_Groups'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include __DIR__ . '/Object.php';
    }

    class CAT_Groups extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected        $groups   = array();
        protected        $roles    = array();
        protected static $instance = NULL;

        /**
         * create a new groups object
         * @access public
         * @return object
         **/
        public function __construct($id=NULL)
        {
            parent::__construct();
            $this->initGroups();
        }   // end function __construct()

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
        public function addGroup($name,$description)
        {
            $sth = CAT_Helper_DB::getInstance()->query(
                  'INSERT INTO `:prefix:rbac_groups` ( `title`, `description` ) '
                . 'VALUES ( :name, :desc )',
                array('name'=>$name,'desc'=>$description)
            );
        }   // end function addGroup()

        /**
         *
         * @access public
         * @return
         **/
        public function exists($item)
        {
            if(!$this->groups) $this->initGroups();
            foreach($this->groups as $group)
            {
                if(is_numeric($item)  && $group['group_id'] == $item) return true;
                if(!is_numeric($item) && strcasecmp($group['title'],$item)) return true;
            }
            return false;
        }   // end function exists()

        /**
         *
         * @access public
         * @return
         **/
        public function getGroup($id)
        {
            if(!$this->groups) $this->initGroups();
            foreach($this->groups as $group)
            {
                if($group['group_id'] == $id) return $group;
            }
            return false;
        }   // end function getGroup()
        

        /**
         * get a list of groups; optional $user_id
         *
         * @access public
         * @param  integer  $user_id - member id
         * @return
         **/
        public function getGroups($user_id=NULL)
        {
            if($user_id)
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
                    array('id'=>$user_id)
                );
                return $sth->fetchAll(\PDO::FETCH_ASSOC);
            }
            return $this->groups;
        }   // end function getGroups()

        /**
         *
         * @access public
         * @return
         **/
        public function getMembers($group_id)
        {
            $q    = 'SELECT * FROM `:prefix:rbac_groups` AS t1 '
                  . 'JOIN `:prefix:rbac_usergroups` AS t2 '
                  . 'ON `t1`.`group_id`=`t2`.`group_id` '
                  . 'JOIN `:prefix:rbac_users` AS t3 '
                  . 'ON `t2`.`user_id`=`t3`.`user_id` '
                  . 'WHERE `t1`.`group_id` = :id'
                  ;
            $dbh  = CAT_Helper_DB::getInstance();
            $sth  = $dbh->query(
                $q, array('id'=>$group_id)
            );
            return $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function getMembers()

        /**
         *
         * @access public
         * @return
         **/
        public function removeGroup($id)
        {
            $dbh  = CAT_Helper_DB::getInstance();
            $sth  = $dbh->query(
                'DELETE FROM `:prefix:rbac_groups` WHERE `group_id`=:id',
                array('id'=>$id)
            );
            if(!$dbh->isError()) return true;
            else                 return false;
        }   // end function removeGroup()

        /**
         *
         * @access public
         * @return
         **/
        public function set($field,$value,$id)
        {
            $dbh  = CAT_Helper_DB::getInstance();
            $sth  = $dbh->query(
                'UPDATE `:prefix:rbac_groups` SET `:fieldname:`=:value WHERE `group_id`=:id',
                array('fieldname'=>$field,'value'=>$value,'id'=>$id)
            );
            if(!$dbh->isError()) return true;
            else                 return false;
        }   // end function set()

        /**
         *
         * @access protected
         * @return
         **/
        protected function initGroups()
        {
            $dbh  = CAT_Helper_DB::getInstance();
            $sth  = $dbh->query(
                'SELECT * FROM `:prefix:rbac_groups`'
            );
            $this->groups = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }   // end function initGroups()

        /**
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
         * get group roles
         *
         * @access protected
         * @return void
         **/
        protected function initRoles()
        {
            $this->roles = CAT_Roles::getInstance()->getRoles(
                array(
                    'for' => 'group'
                )
            );
        }   // end function initRoles()
    }
}