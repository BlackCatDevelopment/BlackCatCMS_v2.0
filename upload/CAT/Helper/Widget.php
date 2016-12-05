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

if (!class_exists('CAT_Helper_Widget'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Widget extends CAT_Object
    {
        private   static $instance;
        //protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $loglevel = \Monolog\Logger::DEBUG;

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        public function __call($method, $args)
        {
            if(!isset($this) || !is_object($this))
                return false;
            if(method_exists($this,$method))
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         *
         * @access public
         * @return
         **/
        public static function exists($id)
        {
            $self = self::getInstance();
            $field = ( is_numeric($id) ? 'widget_id' : 'widget_name' );
            $sth  = $self->db()->query(
                'SELECT * FROM `:prefix:dashboard_widgets` WHERE `'.$field.'`=?',
                array($id)
            );
            return $sth->rowCount();
        }   // end function exists()
        

        /**
         * gets the list of widgets a user is allowed to see
         *
         * @access public
         * @return
         **/
        public static function getAllowed($global=false,$alldata=false)
        {
            $self  = self::getInstance();
            // all data or json data?
            if($alldata) $fields = '*';
            else         $fields = 't2.widget_id,widget_name,preferred_column,icon';
            // get query builder (save some typing)
            $query = $self->db()->qb();
            // basics
            $query->select($fields)
                  ->from($self->db()->prefix().'dashboard_widget_permissions','t1')
                  ->rightJoin('t1',$self->db()->prefix().'dashboard_widgets','t2','t1.widget_id=t2.widget_id')
                  ;

            if($global)
            {
                $query->where('allow_in_global=?')
                      ->setParameter(0, 'Y');
            }

            // root is allowed all
            if(!$self->user()->is_root())
            {
                // get the user's groups
                $groups = $self->user()->getGroups(1);
                $query->andWhere(
                    $query->expr()->orX(
                        'needed_group IS NULL',
                        'needed_group IN (:ids)'
                    )
                )->setParameter('ids', array_values($groups), \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            }

            $sth = $query->execute();
            $data = $sth->fetchAll();

            return $data;
        }   // end function getAllowed()

        /**
         *
         * @access public
         * @return
         **/
        public static function getWidget($id)
        {
            $self  = self::getInstance();
            $sth   = $self->db()->query(
                  'SELECT * FROM `:prefix:dashboard_widgets` AS `t1` '
                . 'RIGHT JOIN `:prefix:dashboard_widget_data` AS `t2` '
                . 'ON `t1`.`widget_id`=`t2`.`widget_id` '
                . 'WHERE `widget_id`=?',
                array($id)
            );
echo "<textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
print_r( $self->db()->getLastStatement(array($id)) );
echo "</textarea>";
            return $sth->fetch();
        }   // end function getWidget()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function saveWidgetData($widget_id,$dash_id,$data)
        {
            $self  = self::getInstance();
            $sth   = $self->db()->query(
                'REPLACE INTO `:prefix:dashboard_widget_data` VALUES (?,?,?)',
                array($widget_id,$dash_id,serialize($data))
            );
        }   // end function saveWidgetData()
        

    }

}