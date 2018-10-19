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
   @package         coreVisitorStatistics

*/

namespace CAT\Addon;

use \CAT\Base as Base;

if(!class_exists('\CAT\Addon\coreVisitorStatistics',false))
{
    final class coreVisitorStatistics extends Tool
    {
        protected static $type        = 'tool';
        protected static $directory   = 'coreVisitorStatistics';
        protected static $name        = 'Visitor Statistics';
        protected static $version     = '0.1';
        protected static $description = "Show page impressions";
        protected static $author      = "Black Cat Development";
        protected static $guid        = "";
        protected static $license     = "GNU General Public License";

        /**
         *
         * @access public
         * @return
         **/
        public static function tool()
        {
            $stmt = self::db()->query(
                  'SELECT `t1`.*, `t2`.`menu_title` '
                . 'FROM `:prefix:pages_visits` AS `t1` '
                . 'JOIN `:prefix:pages` AS `t2` '
                . 'ON `t1`.`page_id`=`t2`.`page_id` '
                . 'ORDER BY `last` DESC'
            );
            
            return self::tpl()->get('tool', array(
                'data' => $stmt->fetchAll()
            ));
        }   // end function tool()

    }
}