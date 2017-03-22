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

if ( !class_exists( 'CAT_Helper_Addons' ) )
{
    if ( !class_exists( 'CAT_Object', false ) )
    {
        @include dirname( __FILE__ ) . '/../Object.php';
    }

    class CAT_Helper_Addons extends CAT_Object
    {
        /**
         * log level
         **/
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        /**
         * instance
         **/
        private   static $instance = NULL;

        public function __construct() {}

        public function __call( $method, $args )
        {
            if ( !isset( $this ) || !is_object( $this ) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array( array(
                     $this,
                    $method
                ), $args );
        }   // end __call()

        public static function getInstance()
        {
            if ( !self::$instance )
                self::$instance = new self();
            return self::$instance;
        } // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function exists($addon)
        {
            $name = self::getAddonDetails($addon,'name');
            return ($name && strlen($name)) ? true : false;
        }   // end function exists()
        
        /**
         * gets the details of an addon
         *
         * @access public
         * @param  string  ID or directory name
         * @return mixed   array on success, NULL otherwise
         **/
        public static function getAddonDetails($addon,$field='*')
        {
            // sanitize column name
            if(!in_array($field,array('*','addon_id','type','directory','name','description','function','version','guid','platform','author','license','installed','upgraded','removable','bundled')))
                return NULL; // silently fail
            $q = 'SELECT `%s` FROM `:prefix:addons` WHERE ';
            if(is_numeric($addon)) $q .= '`addon_id`=:val';
            else                   $q .= '`directory`=:val';
            $addon = self::db()->query(
                sprintf($q,$field),
                array('val'=>$addon)
            );
            if($addon->rowCount())
            {
                $data = $addon->fetch(\PDO::FETCH_ASSOC);
                if($field!='*') return $data[$field];
                else            return $data;
            }
            return NULL;
        } // end function getAddonDetails()

        /**
         * Function to get installed addons
         *
         * @access public
         * @param  int     $selected    (default: 1)      - name or directory of the the addon to be selected in a dropdown
         * @param  string  $type        (default: '')     - type of addon - can be an array
         * @param  string  $function    (default: '')     - function of addon- can be an array
         * @param  string  $order       (default: 'name') - value to handle "ORDER BY" for database request of addons
         * @param  boolean $check_permission (default: false) - wether to check module permissions (BE call) or not
         * @return array
         */
        public static function getAddons($selected=1, $type=NULL, $function=NULL, $order='name', $check_permission=false, $find_icon=false )
        {
            // create query builder
            $q = CAT_Helper_DB::qb()
                ->select('*')
                ->from(sprintf('%saddons',CAT_TABLE_PREFIX));

            // filter by type
            if($type) {
                if(is_array($type)) {
                    foreach($type as $item) {
                        $q->andWhere('type = '.$q->createNamedParameter($item));
                    }
                } else {
                    $q->andWhere('type = '.$q->createNamedParameter($type));
                }
            }

            // filter by module function
            if($function) {
                if(is_array($function)) {
                    foreach($function as $item) {
                        $q->andWhere('function = '.$q->createNamedParameter($item));
                    }
                } else {
                    $q->andWhere('function = '.$q->createNamedParameter($function));
                }
            }

            $q->orderBy('type', 'ASC'); // default order
            if($order && $order != 'name')
                $q->addOrderBy($order, 'ASC NULLS FIRST');

            // get the data
            $data = $q->execute()->fetchAll();

            for($i=(count($data)-1);$i>=0;$i--)
            {
                $addon = $data[$i];
                if(!self::user()->hasModulePerm($addon['addon_id']))
                {
                    unset($data[$i]); // not allowed
                }
                if($find_icon)
                {
                    $icon = CAT_Helper_Directory::sanitizePath(CAT_ENGINE_PATH.'/'.$addon['type'].'s/'.$addon['directory'].'/icon.png');
                    $data[$i]['icon'] = '';
                    if(file_exists($icon)){
                        list($width, $height, $type_of, $attr) = getimagesize($icon);
                        // Check whether file is 32*32 pixel and is an PNG-Image
                        $data[$i]['icon']
                            = ($width == 32 && $height == 32 && $type_of == 3)
                            ? CAT_URL.'/'.$addon['type'].'s/'.$addon['directory'].'/icon.png'
                            : false
                            ;
                    }
                }
            }
            return $data;
        } // end function getAddons()



    } // class CAT_Helper_Addons

} // if class_exists()
