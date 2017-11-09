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

if (!class_exists('CAT_Helper_Zip'))
{
    class CAT_Helper_Zip extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
	
        private   static $_drivers = array();
        private   static $zip;

	    /**
	     * constructor
	     **/
		public function __construct($zipfile=NULL)
        {
            // get driver
            self::$zip = self::getDriver('PclZip',$zipfile);
		}   // end function __construct()

        /**
         * forward unknown methods to driver
         *
         */
        public function __call($method,$attr)
        {
            return self::$zip->$method($attr);
        }   // end function __call()

		/**
         * try to load the driver
		 * 
         * @access private
         * @param  string  $driver  - driver name
         * @param  string  $zipfile - optional zip file name
         * @return object
		 **/
        private static function getDriver($driver,$zipfile=NULL)
		{
            if(!preg_match('/driver$/i',$driver))
                $driver .= 'Driver';

            if(!isset(self::$_drivers[$driver]) || !is_object(self::$_drivers[$driver]))
            {
                if(!file_exists(dirname(__FILE__).'/Zip/'.$driver.'.php'))
			    {
                    self::printFatalError('No such Zip driver: ['.$driver.']');
			    }
                require dirname(__FILE__).'/Zip/'.$driver.'.php';
                $driver = 'CAT_Helper_Zip_'.$driver;
                self::$_drivers[$driver] = $driver::getInstance($zipfile);
            }
            return self::$_drivers[$driver];
        }   // end function getDriver()

        public function config($option,$value=NULL) { return self::$zip->config($option,$value); }
        public function add($args)                  { return self::$zip->add($args);       }
        public function create($args)               { return self::$zip->create($args);    }
        public function extract()                   { return self::$zip->extract();           }
        public function extractByIndex($args)       { return self::$zip->extractByIndex($args);  }
        public function errorInfo($p_full=false)    { return self::$zip->errorInfo($p_full);     }
			
    }   // end class Cat_Helper_Zip

}   // end class_exists()