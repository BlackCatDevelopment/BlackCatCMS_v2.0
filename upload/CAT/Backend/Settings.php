<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Backend_Settings'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../../Object.php';
    }

    class CAT_Backend_Settings extends CAT_Object
    {
        protected static $instance    = NULL;
        protected static $perm_prefix = 'settings_';
        private   static $regions     = NULL;

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
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * get data from settings table
         **/
        public static function getSettingsTable() {
            $settings = CAT_Registry::getSettings();
            $data     = array();
            foreach($settings as $key => $value)
            {
                $data[strtolower($key)] = $value;
            }
            return $data;
        }   // end function getSettingsTable()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function index($region='?')
        {
            $self = CAT_Backend_Settings::getInstance();
            if(!self::$regions)
            {
                self::$regions  = array();
                $regions = CAT_Backend::getMainMenu(4);
                foreach(array_values($regions) as $r)
                    array_push(self::$regions,$r['name']);
            }

            if($region=='?' || !in_array($region,self::$regions)) // invalid call!
            {
                $region = 'index';
            }
            
            CAT_Backend::print_header();
            $self->tpl()->output('backend_settings',array('region'=>$self->lang()->t(ucfirst($region))));
            CAT_Backend::print_footer();
        }   // end function mail()
        

    } // class CAT_Helper_Settings

} // if class_exists()