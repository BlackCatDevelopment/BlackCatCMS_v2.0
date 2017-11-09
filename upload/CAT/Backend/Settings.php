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

if (!class_exists('CAT_Backend_Settings'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Settings extends CAT_Object
    {
        // log level
        protected static $loglevel       = \Monolog\Logger::EMERGENCY;
        //protected static $loglevel  = \Monolog\Logger::DEBUG;
        protected static $instance       = NULL;
        protected static $perm_prefix    = 'settings_';
        private   static $regions        = NULL;
        private   static $avail_settings = NULL;

        public static function __callstatic($name,$arguments)
        {
            call_user_func([__CLASS__, 'index'] ,$name);
        }   // end function __callstatic()

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
            {
                self::$instance = new self();
                self::addLangFile(__dir__.'/languages');
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * get available settings
         **/
        public static function getSettings()
        {
            if(!self::$avail_settings)
            {
                $data = self::db()->query(
                    'SELECT * FROM `:prefix:settings` AS `t1` '
                    . 'JOIN `:prefix:forms_fieldtypes` AS `t2` '
                    . 'ON `t1`.`fieldtype`=`t2`.`type_id` '
                    . 'WHERE `is_editable`=? '
                    . 'ORDER BY `region`',
                    array('Y')
                );
                if($data)
                {
                    self::$avail_settings = $data->fetchAll();
                }
            }
            return self::$avail_settings;
        }   // end function getSettings()

        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $settings = self::getSettings();
            if(!is_array($settings) || !count($settings))
                self::printFatalError('missing settings!');

            // there *may* be a region name
            $region     = self::router()->getParam();
            if(!$region)
                $region = self::router()->getFunction();

            // filter settings by region
            if($region && $region != 'index')
                $settings = CAT_Helper_Array::filter($settings,'region',$region,'matching');

            if(!self::asJSON())
            {
                $form = self::renderForm($settings);
                CAT_Backend::print_header();
                self::tpl()->output(
                    'backend_settings',
                    array(
                        'form'   => $form->render(true),
                    )
                );
                CAT_Backend::print_footer();
            }
        }   // end function index()
        
        /**
         *
         * @access protected
         * @return
         **/
        protected static function renderForm($settings)
        {
            return CAT_Helper_FormBuilder::generate(
                'settings',
                $settings,
                'region',
                CAT_Object::loadSettings()
            );
        }   // end function renderForm()
        
    } // class CAT_Backend_Settings
} // if class_exists()