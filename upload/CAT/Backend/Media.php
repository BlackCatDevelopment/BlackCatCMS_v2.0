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

if (!class_exists('CAT_Backend_Media'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Media extends CAT_Object
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
self::list();
            $tpl_data = array();
            CAT_Backend::print_header();
            $self->tpl()->output('backend_media', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function media()

        /**
         *
         * @access public
         * @return
         **/
        public static function list()
        {
            $self   = self::getInstance();
            $filter = CAT_Helper_Validate::sanitizePost('filter');
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Benutzer-Homeverzeichnis berücksichtigen
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $data  = CAT_Helper_Media::getMediaFromDir(CAT_PATH.'/media',$filter);
            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($data,true);
                return;
            }
        }   // end function list()
        

    } // class CAT_Helper_Media

} // if class_exists()