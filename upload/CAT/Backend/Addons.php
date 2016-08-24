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

if (!class_exists('CAT_Backend_Addons'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Addons extends CAT_Object
    {
        protected static $instance = NULL;

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
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $self = self::getInstance();
            $data = CAT_Helper_Addons::get_addons(0,NULL,NULL,NULL,false,true);
            foreach($data as $i => $item)
            {
                $data[$i]['install_date'] = CAT_Helper_DateTime::getDate($item['installed']);
                $data[$i]['update_date'] = CAT_Helper_DateTime::getDate($item['upgraded']);
            }
            $tpl_data = array(
                'modules' => $data,
                'modules_json' => json_encode($data, JSON_NUMERIC_CHECK),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_addons', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function Addons()
        

    } // class CAT_Helper_Addons

} // if class_exists()