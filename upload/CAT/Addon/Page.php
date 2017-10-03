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


if (!class_exists('CAT_Addon_Page', false))
{
	if (!interface_exists('CAT_Addon_Page_Int', false))
	{
		interface CAT_Addon_Page_Int {
			public static function add();
			public static function remove();
			public static function view($section_id);
		}
	}
	abstract class CAT_Addon_Page extends CAT_Addon_Module implements CAT_Addon_Page_Int
	{
		/**
		 * @var void
		 */
		protected static $type		= 'page';
		protected static $addonID	= NULL;
        protected static $template  = NULL;

		public function __construct()
		{
			parent::__construct();
		}
		public function __destruct()
		{
			parent::__destruct();
		}

        /**
         * default add function; override to add your own actions
         **/
		public static function add()
		{
			self::setIDs();

			// Add a new section
			if ( self::db()->query(
					'INSERT INTO `:prefix:mod_' . static::$directory . '`
						( `page_id`, `section_id` ) VALUES
						( :page_id, :section_id )',
					array(
						'page_id'		=> self::$page_id,
						'section_id'	=> self::$section_id
					)
				)
			) {
				self::$addonID	= self::db()->lastInsertId();
				return self::$addonID;
			}
			else return NULL;
		}

        /**
         * default view function
         **/
		public static function view($section_id)
		{
			self::$template	= 'view';

			self::tpl()->setPath(CAT_PATH.'/modules/'.static::$directory.'/templates/'.self::getVariant());
			self::tpl()->setFallbackPath(CAT_PATH.'/modules/'.static::$directory.'/templates/default');
			
			self::tpl()->output(
				self::$template,
				array(
                    'section_id' => $section_id
                ) //self::getParserValue()
			);
		}

        /**
         * default remove function
         **/
		public static function remove()
		{
			// Remove from database 
			if( self::db()->query(
				'DELETE FROM `:prefix:mod_' . static::$directory . '` ' .
					'WHERE `page_id` =:page_id ' .
					'AND `section_id` =:section_id',
				array(
					'page_id'		=> self::$page_id,
					'section_id'	=> self::$section_id
				)
			) ) return true;
			else return false;
		}

	}
}