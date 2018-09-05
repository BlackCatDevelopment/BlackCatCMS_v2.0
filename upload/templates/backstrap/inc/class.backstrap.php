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
   @package         catMenuAdmin

*/

namespace CAT\Addon\Template;

if(!class_exists('\CAT\Addon\Template\backstrap',false))
{
    final class backstrap extends \CAT\Addon\Template
    {
        protected static $type        = 'theme';
        protected static $directory   = 'backstrap';
        protected static $name        = 'Backstrap';
        protected static $version     = '0.1';
        protected static $description = "Backstrap Backend Theme";
        protected static $author      = "BlackCat Development";
        protected static $guid        = "";
        protected static $license     = "GNU General Public License";
    }
}