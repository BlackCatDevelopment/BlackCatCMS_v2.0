<?php

/**
 *    @author          Black Cat Development
 *    @copyright       2016, Black Cat Development
 *    @link            http://blackcat-cms.org
 *    @license         http://www.gnu.org/licenses/gpl.html
 *    @category        CAT_Core
 *    @package         CAT_Core
 **/

if (!class_exists('CAT_Permissions'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include __DIR__ . '/Object.php';
    }

    class CAT_Permissions extends CAT_Object
    {
        protected        $perms         = NULL;
        protected        $perms_by_role = NULL;
        protected static $instance      = NULL;
        protected static $loglevel      = \Monolog\Logger::EMERGENCY;

        /**
         * create a new permissions object
         *
         * @access public
         * @return object
         **/
        public function __construct()
        {
            parent::__construct();
            if(!$this->perms)
                $this->initPerms();
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
         * returns the permissions for the given role
         *
         * @access public
         * @param  integer  $role_id
         * @return array
         **/
        public function getPerms($role_id=NULL)
        {
            if(!$this->perms) $this->initPerms();

            if($role_id)
            {
                if(!isset($this->perms_by_role[$role_id]))
                {
                    $sth = $this->db()->query(
                          'SELECT * FROM `:prefix:rbac_permissions` AS `t1` '
                        . 'JOIN `:prefix:rbac_rolepermissions` AS `t2` '
                        . 'ON `t1`.`perm_id`=`t2`.`perm_id` '
                        . 'WHERE `t2`.`role_id`=?',
                        array($role_id)
                    );
                    $data = $sth->fetchAll(\PDO::FETCH_ASSOC);
                    $this->perms_by_role[$role_id] = $data;
                    return $data;
                }
            }

            return $this->perms;
        }   // end function getPerms()

        /**
         * init permissions; loads the permissions assigned to each role and
         * stores the assignments into protected 'perms' array
         *
         * @access protected
         * @return
         **/
        protected function initPerms()
        {
            // do not use $self here, it will lead to infinite loop!
            $roles = CAT_Roles::getInstance()->getRoles();
            if(!is_array($roles) || !count($roles)) return array();

            $q   = 'SELECT * FROM `:prefix:rbac_permissions` ORDER BY `area`,`requires`,`position`';
            $sth = CAT_Helper_DB::getInstance()->query($q);
            $this->perms = $sth->fetchAll(\PDO::FETCH_ASSOC);

            // translate description
            foreach($this->perms as $i => $item)
            {
                $this->perms[$i]['description'] = $this->lang()->t($item['description']);
            }
        }   // end function initPerms()

    } // class CAT_Roles

} // if class_exists()