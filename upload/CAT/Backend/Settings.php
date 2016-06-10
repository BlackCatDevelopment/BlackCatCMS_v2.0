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
        // array to store config options
        protected $_config         = array( 'loglevel' => 7 );
        protected static $instance = NULL;
        protected static $perm_prefix = 'settings_';

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * get the main menu (settings sections)
         * checks the user priviledges
         *
         * @access public
         * @return array
         **/
        public static function getMainMenu($current=NULL)
        {
            $menu = array();
            $self = self::getInstance();

            foreach(array_values(array('seo','frontend','headers','backend','system','users','datetime','searchblock','server','mail','security','sysinfo')) as $item)
            {
                if($self->user()->hasPerm(self::$perm_prefix.$item))
                {
                    $menu[] = array(
                        'link'             => CAT_ADMIN_URL.'/settings/'.$item,
                        'title'            => $self->lang()->translate(ucfirst($item)),
                        'name'             => $item,
                        'current'          => ( $current && $current == $item ) ? true : false
                    );
                }
            }
            return $menu;
        }

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
        public static function index()
        {
            $self = self::getInstance();
            $tpl_data = array(
                'SETTINGS_MENU' => self::getMainMenu(),
            );

            // add default form
            $form = CAT_Backend::getInstance()->getForms('settings');
            $form->setForm('seo');
            $form->set('contentonly',true);
            $values = self::getSettingsTable();
            $form->setData($values);
            //$tpl_data['form'] = $form->getForm();

            $tpl_data['content'] = $self->tpl()->get(
                'backend_settings_seo',
                array('values'=>$values,'form'=>$form->getForm())
            );

            CAT_Backend::print_header();
            $self->tpl()->output('backend_settings', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function Settings()
        

    } // class CAT_Helper_Settings

} // if class_exists()