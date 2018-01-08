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

namespace CAT;
use \CAT\Base as Base;
use \CAT\Helper\Assets as Assets;

if (!class_exists('Frontend', false))
{
    class Frontend extends Base
    {
        #protected static $loglevel    = \Monolog\Logger::EMERGENCY;
        protected static $loglevel    = \Monolog\Logger::DEBUG;
        private   static $instance    = array();
        private   static $maintenance = NULL;
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Spaeter konfigurierbar machen!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        private   static $asset_paths = array(
            'css','js','images','eot','fonts'
        );
        private   static $assets = array(
            'css','js','eot','svg','ttf','woff','woff2',
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
            // serve asset files
            $route  = self::router()->getRoute();
            $suffix = pathinfo($route,PATHINFO_EXTENSION);
            if(
                ($type=self::router()->match('~^('.implode('|',self::$asset_paths).')~i'))!==false
                ||
                (strlen($suffix) && in_array($suffix,self::$assets))
            ) {
                if(strlen($suffix) && in_array($suffix,self::$assets))
                {
                    Assets::serve($suffix,$route,true);
                } else {
                    parse_str(self::router()->getQuery(),$files);
                    // remove leading / from all files
                    foreach($files as $i => $f) $files[$i] = preg_replace('~^/~','',$f,1);
                    Assets::serve($type,$files);
                }
                return;
            }
            // forward to modules
            if(self::router()->match('~^modules/~i') && $suffix=='php')
            {
                require CAT_ENGINE_PATH.'/'.self::router()->getRoute();
                return;
            }

            // forward to backend router
            if(Backend::isBackend())
                return Backend::dispatch();

            // internally handled route?
            $func  = substr($route,0,strpos($route,'/'));

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Sollte es zufällig eine Seite geben, die einer internen Route entspricht,
// wird die nie aufgerufen. Ich weiss aber im Moment keine Lösung.
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if(is_callable(array('self',$func)))
            {
                self::router()->setController('Frontend');
                self::router()->setFunction($func);
                self::router()->dispatch();
            }
            $page_id = \CAT\Page::getID();

            // no page found
            if(!$page_id)
            {
                ob_start();
                    $empty_page_bg = Assets::serve('images',array("CAT/templates/empty_page_bg.jpg"));
                ob_end_clean();
                require dirname(__FILE__).'/templates/empty.php';
                exit;
            }

            // get page handler
            $page   = \CAT\Page::getInstance($page_id);
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
                self::$maintenance
                    = \CAT\Registry::get('maintenance_mode') == 'true'
                    ? true
                    : false;
            }
            return self::$maintenance;
        }   // end function isMaintenance()

    }   // end class Frontend
}