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
        /**
         * for version compare
         **/
        private   static $states   = array(
            '.0' => 'dev',
            '.1' => 'preview',
            '.2' => 'alpha',
            '.5' => 'beta',
            '.8' => 'rc',
            '.9' => 'final'
        );

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
            $name = self::getDetails($addon,'name');
            return ($name && strlen($name)) ? true : false;
        }   // end function exists()
        
        /**
         * Function to get installed addons
         *
         * Default: All addons of all types, sorted by type and name, flat array
         * with a list of
         *    <directory> => <name>
         *
         * Please note that $names_only and $find_icon exclude each other
         * (no place for an icon in the flat array, see above)
         * So (example)
         *     getAddons('tool','name',true,true)
         * will never work!!!
         *
         * @access public
         * @param  string  $type       (default: '')     - type of addon - can be an array
         * @param  string  $order      (default: 'name') - value to handle "ORDER BY" for database request of addons
         * @param  boolean $names_only (default: true)   - get only a flat list of names or a complete data array
         * @param  boolean $find_icon  (default: false)  - wether to search for an icon
         * @param  boolean $not_installed (default: false) - only retrieve modules that have no db entry (not installed)
         * @return array
         */
        public static function getAddons($type=NULL,$order='name',$names_only=true,$find_icon=false,$not_installed=false)
        {
            switch($type) {
                case 'javascript':
                    $stmt = self::db()->query(
                        'SELECT * FROM `:prefix:addons_javascripts`'
                    );
                    $data = $stmt->fetchAll();
                    break;
                case 'jquery':
                    $stmt = self::db()->query(
                        'SELECT * FROM `:prefix:addons_javascripts` WHERE `jquery`="Y"'
                    );
                    $data = $stmt->fetchAll();
                    break;
                case 'js':
                case 'css':
                    $stmt = self::db()->query(
                        'SELECT * FROM `:prefix:addons_javascripts` WHERE `jquery`="N"'
                    );
                    $data = $stmt->fetchAll();
                    break;
                default:
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

            // always order by type
            $q->orderBy('type', 'ASC'); // default order
            if($order && $order != 'name')
                $q->addOrderBy($order, 'ASC');

            // get the data
            $data = $q->execute()->fetchAll();

            // remove addons the user is not allowed for
            for($i=(count($data)-1);$i>=0;$i--)
            {
                $addon = $data[$i];
                if(!self::user()->hasModulePerm($addon['addon_id']))
                {
                    unset($data[$i]); // not allowed
                }
                if(!$names_only && $find_icon)
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

            if($not_installed)
            {
                $seen   = CAT_Helper_Array::extract($data,'directory');
                $result = array();
                // scan modules path for modules not seen yet
                foreach(array('modules','templates') as $t)
                {
                    $subdirs = CAT_Helper_Directory::findDirectories(CAT_ENGINE_PATH.'/'.$t);

                    if(count($subdirs))
                    {
                        foreach($subdirs as $dir)
                        {
                            // skip paths starting with __ (sometimes used for deactivating addons)
                            if(substr($dir,0,2) == '__') continue;
                            $info = self::getInfo($dir);
                            if(is_array($info) && count($info))
                                $result[] = $info;
                        }
                    }
                }
                return $result;
            }
                    break;
            } // end switch()

            if($names_only)
                $data = CAT_Helper_Array::extract($data,'name','directory');

            return $data;
        } // end function getAddons()

        /**
         * gets the details of an addon
         *
         * @access public
         * @param  string  ID or directory name
         * @return mixed   array on success, NULL otherwise
         **/
        public static function getDetails($addon,$field='*')
        {
            // sanitize column name
            if(!in_array($field,array('*','addon_id','type','directory','name','description','function','version','guid','platform','author','license','installed','upgraded','removable','bundled')))
                return NULL; // silently fail
            $q = 'SELECT %s FROM `:prefix:addons` WHERE ';
            if(is_numeric($addon)) $q .= '`addon_id`=:val';
            else                   $q .= '`directory`=:val';
            $addon = self::db()->query(
                sprintf($q,($field != '*' ? '`'.$field.'`' : $field)),
                array('val'=>$addon)
            );
            if($addon->rowCount())
            {
                $data = $addon->fetch(\PDO::FETCH_ASSOC);
                if($field!='*') return $data[$field];
                else            return $data;
            }
            return NULL;
        } // end function getDetails()

        /**
         *
         * @access public
         * @return
         **/
        public static function getInfo($directory)
        {
            $info    = array();
            $fulldir = CAT_ENGINE_PATH.'/modules/'.$directory.'/inc';

            if(is_dir($fulldir))
            {
                // find class.<modulename>.php
                $files = CAT_Helper_Directory::findFiles($fulldir,array('extension'=>'php','remove_prefix'=>true));
                if(count($files)==1)
                {
                    $classname = str_ireplace('class.','',pathinfo($files[0],PATHINFO_FILENAME));
                    if(!class_exists($classname,false))
                    {
                        require_once $fulldir.'/'.$files[0];
                    }
                    $info = $classname::getInfo();
                }
            }
            return $info;
        }   // end function getInfo()
        
        /**
         * removes/replaces known substrings in version string with their
         * weights
         *
         * @access public
         * @param  string  $version
         * @return string
         */
        public static function getVersion($version)
        {
            $version = strtolower($version);

            // additional version string, f.e. "beta", to "weight"
            foreach(self::$states as $value => $keys)
                $version = str_replace($keys, $value, $version);
            // remove blanks, replace comma
            $version = str_replace(
                array(" ",','),
                array("",''),
                $version
            );
            /**
             *	Force the version-string to get at least 4 terms.
             *	E.g. 2.7 will become 2.7.0.0
             */
            $temp_array = explode( ".", $version );
            $n          = count( $temp_array );
            if($n < 4)
            {
                for($i = 0; $i<(4-$n); $i++)
                    $version = $version . ".0";
            }
            // remove letters ('v1.2.3' => '1.2.3')
            $version = preg_replace('~[a-z]+~i','',$version);
            return $version;
        } // end function getVersion()
        
        /**
         * This function performs a comparison of two provided version strings
         * The versions are first converted into a string following the major.minor.revision
         * convention; the converted strings are passed to version_compare()
         *
         * @access public
         * @param  string  $version1
         * @param  string  $version2
         * @param  string  $operator - default '>='
         */
        public static function versionCompare($version1,$version2,$operator='>=')
        {
            return version_compare(self::getVersion($version1),self::getVersion($version2),$operator);
        } // end versionCompare()

    } // class CAT_Helper_Addons

} // if class_exists()
