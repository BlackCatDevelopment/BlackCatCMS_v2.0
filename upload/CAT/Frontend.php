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

if (!class_exists('CAT_Frontend', false))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/Object.php';
    }

    class CAT_Frontend extends CAT_Object
    {
        protected static $loglevel    = \Monolog\Logger::EMERGENCY;
        private   static $instance    = array();
        private   static $maintenance = NULL;
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Spaeter konfigurierbar machen!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        private   static $asset_paths = array(
            'css','js','images','eot','fonts'
        );

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * dispatch frontend route
         **/
        public static function dispatch()
        {
            $self   = self::getInstance();

            // serve asset files
            if(($type=$self->router()->match('~^('.implode('|',self::$asset_paths).')~i'))!==false)
            {
                parse_str($self->router()->getQuery(),$files);
                // remove leading / from all files
                foreach($files as $i => $f) $files[$i] = preg_replace('~^/~','',$f,1);
                CAT_Helper_Assets::serve($type,$files);
                return;
            }

            // forward to modules
            if($self->router()->match('~^modules/~i'))
            {
                require CAT_ENGINE_PATH.'/'.$self->router()->getRoute();
                return;
            }

            // forward to backend router
            if(CAT_Backend::isBackend())
                return CAT_Backend::dispatch();

            // internally handled route?
            $route = $self->router()->getRoute();
            $func  = substr($route,0,strpos($route,'/'));

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Sollte es zufällig eine Seite geben, die einer internen Route entspricht,
// wird die nie aufgerufen. Ich weiss aber im Moment keine Lösung.
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if(is_callable(array('self',$func)))
            {
                $self->router()->setController('CAT_Frontend');
                $self->router()->setFunction($func);
                $self->router()->dispatch();
            }
            $page_id = CAT_Page::getID();
            // get page handler
            $page   = CAT_Page::getInstance($page_id);
            // hand over to page handler
            $page->show();
        }   // end function dispatch()

        /**
         * check if system is in maintenance mode
         *
         * @access public
         * @return boolean
         **/
        public static function isMaintenance()
        {
            if(!self::$maintenance)
            {
                $self = self::getInstance();
                self::$maintenance
                    = CAT_Registry::get('maintenance_mode') == 'true'
                    ? true
                    : false;
            }
            return self::$maintenance;
        }   // end function isMaintenance()

    }   // end class CAT_Frontend
}