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

namespace CAT\Addon;

use \CAT\Base as Base;
use \CAT\Helper\Addons as Addons;
use \CAT\Helper\Directory as Directory;

if (!class_exists('\CAT\Addon\Module', false))
{
	abstract class Module extends Base implements IAddon
	{
		/**
		 *
		 */
		public function __construct()
		{
			parent::__construct();
		}
		public function __destruct()
		{
			parent::__destruct();
		}

        /**
         * gets the details of an addon
         *
         * @access public
         * @param  string  $value - required info item
         * @return string
         */
        public static function getInfo($value=NULL)
        {
            if($value)
                return static::$$value;
            // get 'em all
            $info = array();
            foreach(array_values(array(
                'name', 'directory', 'version', 'author', 'license', 'description', 'guid', 'home', 'platform', 'type'
            )) as $key) {
                if(isset(static::$$key) && strlen(static::$$key)) {
                    $info[$key] = static::$$key;
                }
            }
            return $info;
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function initialize()
        {
            // if there's something you need to do to initialize your module,
            // overload this method
        }   // end function initialize()

		/**
		 * Default install routine
		 */
		public static function install()
		{
            $errors  = array();
            $sqlfile = Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.static::$directory.'/inc/install.sql');
            if(file_exists($sqlfile))
                $errors	= self::sqlProcess();
			return $errors;
		}

		/**
		 * Default modify routine
		 */
		public static function modify($section_id)
		{
			self::setIDs();
            $tpl_path = Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.static::$directory.'/templates/'.self::getVariant());
            if(is_dir($tpl_path))
                self::tpl()->setPath($tpl_path);
            $def_path = Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.static::$directory.'/templates/default');
            if(is_dir($def_path))
                self::tpl()->setFallbackPath($def_path);
			self::tpl()->output(
				self::$template,
				array() //self::getParserValue()
			);
		}

		/**
		 * Default uninstall routine
		 */
		public static function uninstall()
		{
            $errors  = array();
            $sqlfile = Directory::sanitizePath(CAT_ENGINE_PATH.'/modules/'.static::$directory.'/inc/uninstall.sql');
            if(file_exists($sqlfile))
                $errors	= self::sqlProcess();
			return $errors;
		}

		/**
		 *
		 */
		public static function upgrade() {}
		/**
		 *
		 */
		public static function save($section_id) {}

	}
}