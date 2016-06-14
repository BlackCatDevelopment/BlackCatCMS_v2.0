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

$pg = CAT_Helper_Page::getInstance();

$mod_headers = array(
    'backend' => array(
        'meta' => array(
            array( 'charset' => (defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : "utf-8") ),
            array( 'http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge' ),
            array( 'name' => 'viewport', 'content' => 'width=device-width, initial-scale=1' ),
            array( 'name' => 'description', 'content' => $pg->lang()->translate('Administration') ),
            array( 'name' => 'keywords', 'content' => $pg->lang()->translate('Administration') ),
        ),
        'css' => array(
            array('file'=>'modules/lib_bootstrap/vendor/css/bootstrap.min.css',),
            array('file'=>'modules/lib_bootstrap/vendor/css/bootstrap-editable.css',),
            array('file'=>'modules/lib_bootstrap/vendor/css/fuelux.min.css',),
            array('file'=>'modules/lib_bootstrap/vendor/css/font-awesome.min.css',),
            array('file'=>'templates/backstrap/css/metisMenu.min.css',),
            array('file'=>'templates/backstrap/css/default/theme.css',),
        ),
        'jquery' => array(
            array(
                'core'    => true,
                'ui'      => true,
                'plugins' => array ('cattranslate'),
            )
        ),
        'js' => array(
            array(
                'backend.js'
            )
        )
    )
);

// check for custom JS for current backend page
if ( CAT_Registry::get('DEFAULT_THEME_VARIANT') != '' ) {
    $variant = CAT_Registry::get('DEFAULT_THEME_VARIANT');
    array_push($mod_headers['backend']['css'], array('file'=>'templates/backstrap/css/'.$variant.'/colors.css'));
}
else
{
    array_push($mod_headers['backend']['css'], array('file'=>'templates/backstrap/css/default/colors.css'));
}

