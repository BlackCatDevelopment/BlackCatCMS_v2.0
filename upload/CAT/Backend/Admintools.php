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
            $d = CAT_Helper_Dashboard::getDashboardConfig('backend/admintools');
            // no configuration yet
            if(!isset($d['widgets']) || !is_array($d['widgets']) || !count($d['widgets']))
            {
                $tools = CAT_Helper_Addons::get_addons(0,'module','tool');
                $col          = 1; // init column
                $d['columns'] = ( isset($d['columns']) ? $d['columns'] : 2 ); // init col number
                if(count($tools))
                {
                    // order tools by name
                    $tools = CAT_Helper_Array::ArraySort($tools,'name','asc',true);
                    $count = count($tools);
                    foreach($tools as $tool)
                    {
/*
[addon_id] => 1
[type] => module
[directory] => blackcat
[name] => BlackCat CMS Admin Tool and Widget
[description] => BlackCat CMS Admin Tool and Widget - allows to check for new versions (widget demo)
[function] => tool
[version] => 0.6
[guid] => CF217773-24C7-4DAB-954F-98D9F7118F7D
[platform] => 1.0
[author] => BlackCat  Development
[license] => GNU General Public License
[installed] => 1458312789
[upgraded] => 1458312789
[removable] => Y
[bundled] => Y
*/
                        // init widget
                        $d['widgets'][] = array(
                            'column'        => $col,
                            'widget_title'  => '<a href="">'.$tool['name'].'</a>',
                            'content'       => $tool['description'],
                            'position'      => 1,
                            'open'          => true,
                        );
                        $col++;
                        if($col > $d['columns']) $col = 1;
                    }
                    //CAT_Helper_Dashboard::saveDashboardConfig($d,'global','admintools');
                    //$d = CAT_Helper_Dashboard::getDashboard('backend/admintools');
                }
            }
            $self = self::getInstance();
            CAT_Backend::print_header();
            $self->tpl()->output('backend_dashboard',array('id'=>0,'dashboard'=>$d));
            CAT_Backend::print_footer();

        }   // end function Admintools()
        

    } // class CAT_Helper_Admintools

} // if class_exists()