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


if (!class_exists('CAT_Addon_Module', false))
{
	if (!interface_exists('CAT_Addon_Module_Int', false))
	{
		interface CAT_Addon_Module_Int {
			public static function save($section_id);
			public static function modify($section_id);
			public static function install();
			public static function uninstall();
			public static function upgrade();
		}
	}
	abstract class CAT_Addon_Module extends CAT_Addon implements CAT_Addon_Module_Int
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
			// static::$directory needs to be checked if this works
			$errors	= self::sqlProcess(CAT_PATH . '/modules/' . static::$directory . '/inc/install.sql');

			$addons_helper = new CAT_Helper_Addons();
			foreach(
				array(
					'save.php'
				)
				as $file
			) {
				if ( false === $addons_helper->sec_register_file( static::$directory, $file ) )
				{
					 error_log( "Unable to register file -$file-!" );
				}
			}
			return $errors;
		}

		/**
		 * Default uninstall routine
		 */
		public static function uninstall()
		{
			$errors	= self::sqlProcess($CAT_PATH . '/modules/' . static::$directory . '/inc/uninstall.sql');
			return $errors;
		}

		/**
		 *
		 */
		public abstract static function upgrade();
		/**
		 *
		 */
		public abstract static function save($section_id);

		/**
		 * Default modify routine
		 */
		public static function modify($section_id)
		{
			global $parser;

			self::setIDs();

			// Should be moved to the Object
			//self::setParserValue();

			$parser->setPath(CAT_PATH.'/modules/'.static::$directory.'/templates/'.self::getVariant());
			$parser->setFallbackPath(CAT_PATH.'/modules/'.static::$directory.'/templates/default');

			$parser->output(
				self::$template,
				array() //self::getParserValue()
			);
		}

	}
}