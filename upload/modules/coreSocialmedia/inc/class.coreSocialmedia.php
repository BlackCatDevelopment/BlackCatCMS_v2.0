<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2018 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Module
   @package         coreSocialmedia

*/

namespace CAT\Addon;

use \CAT\Base as Base;

if(!class_exists('\CAT\Addon\coreSocialmedia',false))
{
    final class coreSocialmedia extends Tool
    {
        protected static $type        = 'tool';
        protected static $directory   = 'coreSocialmedia';
        protected static $name        = 'Socialmedia';
        protected static $version     = '0.1';
        protected static $description = "Manage your Social Media Services here";
        protected static $author      = "BlackCat Development";
        protected static $guid        = "";
        protected static $license     = "GNU General Public License";

        /**
         *
         * @access public
         * @return
         **/
        public static function edit()
        {
            // check permissions
            if(!self::user()->hasPerm('socialmedia_site_edit'))
                self::printFatalError('You are not allowed for the requested action!');

            // field name
            $field = \CAT\Helper\Validate::get('name','string');
            // new value
            $value = \CAT\Helper\Validate::get('value','string');
            // id
            $id    = \CAT\Helper\Validate::get('pk','numeric');

            if($field && $value && $id) {
                $table = 'socialmedia';
                if($field=='account') {
                    $table .= '_site';
                }
                self::db()->query(
                    'UPDATE `:prefix:'.$table.'` SET `:field:`=:value WHERE `id`=:id',
                    array('field'=>$field,'value'=>$value,'id'=>$id)
                );
            }
        }   // end function edit()
        

        /**
         *
         * @access public
         * @return
         **/
        public static function tool()
        {
            // get available services
            $services = \CAT\Helper\Socialmedia::getServices(CAT_SITE_ID);
            return self::tpl()->get(
                    'tool',
                    array(
                        'services' => $services
                    )
                );
        }   // end function tool()

    }
}