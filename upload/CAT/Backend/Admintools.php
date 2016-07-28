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

if (!class_exists('CAT_Backend_Admintools'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Admintools extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 7 );
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
            $self  = self::getInstance();
            if(!$self->user()->hasPerm('tools_list'))
                CAT_Object::json_error('You are not allowed for the requested action!');
            $tools = CAT_Helper_Addons::get_addons(0,'module','tool');
            if(count($tools))
            {
                foreach($tools as $tool)
                {
                    // check if the user is allowed to see this item
                    #if(!$user->get_permission($tool['directory'],$tool['type']))
                    #    continue;

                    // check if a module description exists for the displayed backend language
                    $module_description = false;
                    $icon               = false;
                    $language_file      = CAT_PATH.'/modules/'.$tool['VALUE'].'/languages/' . $self->lang()->getLang() . '.php';
                    if ( true === file_exists($language_file) )
                        require $language_file;
                    // Check whether icon is available for the admintool
                    if ( file_exists(CAT_PATH.'/modules/'.$tool['VALUE'].'/icon.png') )
                    {
                        list($width, $height, $type, $attr) = getimagesize(CAT_PATH.'/modules/'.$tool['VALUE'].'/icon.png');
                        // Check whether file is 32*32 pixel and is an PNG-Image
                        $icon = ($width == 32 && $height == 32 && $type == 3)
                              ? CAT_URL.'/modules/'.$tool['VALUE'].'/icon.png'
                              : false;
                    }
                    $tpl_data['tools'][] = array(
                        'TOOL_NAME'        => $tool['NAME'],
                        'TOOL_DIR'         => $tool['VALUE'],
                        'ICON'             => $icon,
                        'TOOL_DESCRIPTION' => (!$module_description?$tool['description']:$module_description),
                    );
                }
            }
            $tpl_data['tools_count'] = count($tpl_data['tools']);

            CAT_Backend::print_header();
            $self->tpl()->output('backend_admintools', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function Admintools()
        

    } // class CAT_Helper_Admintools

} // if class_exists()