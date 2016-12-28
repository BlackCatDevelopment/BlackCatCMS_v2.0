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
   @package         CAT_Installer

*/

if(!class_exists('CAT_Installer',false))
{
    // Composer autoloader
    require_once CAT_ENGINE_PATH.'/CAT/vendor/autoload.php';
    
    if (!class_exists('CAT_Object', false))
    {
        @include CAT_ENGINE_PATH.'/CAT/Object.php';
    }

    //******************************************************************************
    // register autoloader
    //******************************************************************************
    spl_autoload_register(function($class)
    {
        if(!substr_compare($class, 'wblib', 0, 4)) // wblib2 components
        {
            $file = str_replace(
                '\\',
                '/',
                CAT_Helper_Directory::sanitizePath(
                    CAT_ENGINE_PATH.'/modules/lib_wblib/'.str_replace(
                        array('\\','_'),
                        array('/','/'),
                        $class
                    ).'.php'
                )
            );
            if (file_exists($file))
                @require $file;
        }
        else                                       // BC components
        {
            $file = '/'.str_replace('_', '/', $class);
            $file = CAT_ENGINE_PATH.'/'.$file.'.php';
            if (file_exists($file))
                @require_once $file;
        }
        // next in stack
    });

    class CAT_Installer extends CAT_Object
    {
        public  static $skin        = 'wizard';
        public  static $errors      = array();
        public  static $wizard_url  = NULL;
        public  static $wizard_path = NULL;
        public  static $base_url    = NULL;

        private static $mode        = 'simple';
        private static $tplpath     = CAT_ENGINE_PATH.'/installer/templates/';
        private static $prereqs     = array(
            'phpversion' => '7.0',
            'phpsettings' => array(
                'register_globals' => 0,
        		'safe_mode'        => 0,
            ),

        );
        private static $steps    = array(
            'simple'   => array('database','admin_account','summary','installation'),
            'advanced' => array('globals','database','admin_account','summary','installation'),
        );
        private static $pages    = array(
            'globals' => array(
                'base_url' => 'Base URL',
                'title'    => 'Website title',
                'ssl_available' => 'SSL available',
                'ssl' => 'Require SSL for the Admin Panel',
                'default_timezone' => 'Default Timezone',
            ),
            'database' => array(
                'db_hostname' => 'Hostname',
                'db_port' => 'Port',
                'db_name' => 'Database name',
                'db_username' => 'Admin user',
                'db_prefix' => 'Table prefix',
            ),
            'Admin account' => array(
                'admin_username' => 'Admin user',
                'admin_email' => 'eMail address',
            ),
        );
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // Pruefen, welche Verzeichnisse wirklich noch schreibbar sein muessen
        // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        private static $dirs    = array(
            array( 'name' => ''         , 'ok' => false ), // root
//            array( 'name' => 'media'    , 'ok' => false ),
            array( 'name' => 'templates', 'ok' => false ),
            array( 'name' => 'modules'  , 'ok' => false ),
            array( 'name' => 'languages', 'ok' => false ),
            array( 'name' => 'temp'     , 'ok' => false ),
        );
        private static $data    = array();

        function run()
        {
            self::$wizard_url   = self::getInstUri();
            self::$base_url     = dirname(self::$wizard_url);
            self::$wizard_path  = CAT_ENGINE_PATH.'/installer';

            $current_language = strtoupper(CAT_Registry::get('language',NULL,self::lang()->getLang()));
            self::lang()->addFile(
                $current_language,
                dirname(__FILE__).'/../languages/'
            );

            define('CAT_URL',self::$base_url);

            self::preInstCheck();

            // check errors
            if(count(self::$errors)) {
                include self::$tplpath.'/'.self::$skin.'/pages/precheck.phtml';
                exit;
            }

            // default page
            $page = 'welcome_'.strtolower($current_language); 
            if(isset($_GET['page'])) {
                $page = $_GET['page'];
            }

            // execute installation
            if(isset($_POST['DO'])) {
                echo json_encode(array('success'=>true,'message'=>'installing'));
                exit;
            }

            $template = CAT_Installer::getTemplate($page);

            if(isset($_GET['mode']))
                $mode = $_GET['mode'];

            self::$data = self::getInstData();

            // get / add defaults
            self::getDefaults();

            // remove settings from summary page (simple mode)
            if(self::$mode=='simple') {
                unset(self::$pages['globals']);
            }
            else {
                if(self::$data['ssl_available']!=1) {
                    unset(self::$pages['globals']['ssl_available']);
                    unset(self::$pages['globals']['ssl']);
                }
            }

            // show page
            include $template;
        }   // end function run()

        /**
         * scans template path and fallback path for given template
         **/
        public static function getTemplate($page)
        {
            foreach(array_values(array(self::$tplpath.'/'.self::$skin,self::$tplpath.'/default')) as $dir) {
                if(file_exists($dir.'/pages/'.$page.'.phtml')) {
                    $template = $dir.'/pages/'.$page.'.phtml';
                    return $template;
                }
            }
            return false;
        }   // end function getTemplate()

        /**
         * add default settings
         **/
        protected static function getDefaults()
        {
            // check operating system
            if(!isset(self::$data['operating_system'])) {
                if(substr(php_uname('s'),0,7) == "Windows") { self::$data['operating_system'] = 'windows'; }
                else                                        { self::$data['operating_system'] = 'linux';   }
            }
            // check SSL
            self::$data['ssl_available'] = self::sslCheck();
            if(!isset(self::$data['ssl'])) {
                self::$data['ssl'] = ( self::$data['ssl_available']==true ? 'yes' : 'no' );
            }
            // check base URL
            if(!isset(self::$data['base_url']))
                self::$data['base_url'] = self::$base_url;
            // Website title
            if(!isset(self::$data['title']))
                self::$data['title'] = 'BlackCat CMS';

            // timezone
            // get timezones
            #include INST_PATH.'/../CAT/Helper/DateTime.php';
            self::$data['timezone_table'] = CAT_Helper_DateTime::getInstance()->getTimezones();
            if(!isset(self::$data['default_timezone'])) {
                if(date_default_timezone_get()) {
                    self::$data['default_timezone'] = date_default_timezone_get();
                }
                elseif(ini_get('date.timezone')) {
                    self::$data['default_timezone'] = ini_get('date.timezone');
                }
                else {
                    self::$data['default_timezone'] = "Europe/Berlin";
                }
            }
        }

        /**
         * check if there's already some wizard data
         **/
        protected static function getInstData()
        {
            if(file_exists(self::$wizard_path.'/instdata.tmp') && filesize(self::$wizard_path.'/instdata.tmp')>0) {
                $fh = fopen(self::$wizard_path.'/instdata.tmp','r');
                if($fh && is_resource($fh)) {
                    $data = fread($fh,filesize(self::$wizard_path.'/instdata.tmp'));
                    fclose($fh);
                    $data = json_decode($data,true);
                    return $data;
                }
            }
            return array();
        }   // end function getInstData()

        /**
         * try to "guess" the installer url
         **/
        protected static function getInstUri()
        {
            $url = ( isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http' )
                 . '://'
                 . ( isset($_SERVER['SERVER_NAME'])    ? $_SERVER['SERVER_NAME']    : 'localhost' )
                 . (
                     (
                          isset($_SERVER['SERVER_PORT'])
                       && ($_SERVER['SERVER_PORT']!=80 && $_SERVER['SERVER_PORT']!=443)
                     )
                     ? ':'.$_SERVER['SERVER_PORT']
                     : ''
                   )
                 . $_SERVER["SCRIPT_NAME"]
                 ;
            return $url;
        }   // end function getInstUri()

        /**
         * the pre-installation checks
         **/
        protected static function preInstCheck()
        {
            // PHP Version
            if(!version_compare(PHP_VERSION,self::$prereqs['phpversion'],'>=')) {
                self::$errors[] = array(
                    'check'    => 'PHP-Version',
                    'required' => self::$prereqs['phpversion'],
                    'actual'   => PHP_VERSION
                );
            }
            // PHP Settings
            foreach(self::$prereqs['phpsettings'] as $key => $value) {
                $actual_setting = ($temp=ini_get($key)) ? $temp : 0;
                if($actual_setting !== $value) {
                    self::$errors[] = array(
                        'check'    => 'PHP-Settings -&gt; '.$key,
                        'required' => $value,
                        'actual'   => $actual_setting,
                    );
                }
            }
            // Check if AddDefaultCharset is set
            $sapi  = php_sapi_name();
            if(strpos($sapi,'apache') !== false || strpos($sapi,'nsapi') !== false ) {
            	flush();
            	$apache_rheaders = apache_response_headers();
            	foreach($apache_rheaders as $h) {
            		if(strpos($h,'html; charset') !== false && (!strpos(strtolower($h),'utf-8')) ) {
            			preg_match( '/charset\s*=\s*([a-zA-Z0-9- _]+)/', $h, $match );
            			$apache_charset = $match[1];
            			self::$errors[] = array(
                            'check'    => 'Apache-Settings',
                            'required' => 'unset',
                            'actual'   => $apache_charset,
                        );
            		}
            	}
            }
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Pruefen, welche Verzeichnisse wirklich noch schreibbar sein muessen
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            // file permissions check
            foreach( self::$dirs as $i => $dir ) {
                $path = dirname(__FILE__).'/../../'.$dir['name'];
                if(!is_writable($path)) {
                    self::$errors[] = array(
                        'check'    => sprintf(self::lang()->t('Path').' [%s] '.self::lang()->t('must be writable'),($path==''?'CMS root':$path)),
                        'required' => self::lang()->t('writable'),
                        'actual'   => self::lang()->t('not writable'),
                    );
                }
            }
        }   // end function preInstCheck()

        /**
         * check if SSL is available
         **/
        protected static function sslCheck()
        {
            if(isset($_SERVER['OPENSSL_CONF']) && preg_match('~SSL~',$_SERVER['SERVER_SOFTWARE']))
            {
                #write2log('Seems SSL is available, try to open a socket');
                try {
                    $SSL_Check = @fsockopen("ssl://".$_SERVER['HTTP_HOST'], 443, $errno, $errstr, 30);
                    if (!$SSL_Check) {
                        #write2log(sprintf('fsockopen failed, SSL not available for [%s]',$_SERVER['HTTP_HOST']));
                        return false;
                    } else {
                        #write2log(sprintf('fsockopen succeeded, SSL seems to be available for [%s]',$_SERVER['HTTP_HOST']));
                        fclose($SSL_Check);
                        return true;
                    }
                } catch( Exception $e ) {
                    #write2log(sprintf('exception caught: %s',$e->getMessage()));
                    return false;
                }
            }
        }
    }
}
