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

if (!class_exists('CAT_Helper_Image'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Image extends CAT_Object
    {
        protected static $loglevel     = \Monolog\Logger::EMERGENCY;
        private   static $driver       = NULL;

        /**
         * preferred driver order
         **/
        private   static $drivers      = array(
            'Imagick', 'GD'
        );

        public static function getInstance($file)
        {
            foreach(array_values(self::$drivers) as $name)
            {
                $classname = 'CAT_Helper_Image_'.$name.'Driver';
                $avail = $classname::check_extension();
                if($avail) {
                    self::$driver = new $classname($file);
                    return self::$driver;
                }
            }
            if(!self::$driver) {
                self::printFatalError('No image manipulation library available!');
            }
        }   // end function getInstance()

    }
}

