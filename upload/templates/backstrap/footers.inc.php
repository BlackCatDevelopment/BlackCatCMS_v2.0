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
 *   @author          BlackBird Webprogrammierung
 *   @copyright       2016 BlackBird Webprogrammierung
 *   @link            http://www.webbird.de
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Templates
 *   @package         backstrap
 *
 */

if (defined('CAT_PATH')) {
    include CAT_PATH.'/framework/class.secure.php';
} else {
    $root = "../";
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include $root.'/framework/class.secure.php';
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}

$mod_footers = array(
    'backend' => array(
        'js' => array(
            '/modules/lib_bootstrap/vendor/js/bootstrap.min.js',
            '/modules/lib_bootstrap/vendor/js/bootstrap-editable.min.js',
            '/modules/lib_bootstrap/vendor/js/fuelux.min.js',
            '/modules/lib_jquery/plugins/jquery.timepicker/jquery.timepicker.js',
            '/modules/lib_jquery/plugins/jquery.timepicker/i18n/jquery-ui-timepicker-addon-i18n.min.js',
            '/modules/lib_jquery/plugins/jquery.gridList/gridList.js',
            '/modules/lib_jquery/plugins/jquery.gridList/jquery.gridList.js',
            '/modules/lib_jquery/plugins/jquery.columns/jquery.columns.js',
            '/modules/lib_jquery/plugins/jquery.qtip/jquery.qtip.min.js',
        )
    )
);

