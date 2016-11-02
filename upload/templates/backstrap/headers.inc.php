<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
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

$bootstrapcss = 'modules/lib_bootstrap/vendor/css/bootstrap.min.css';
if(DEFAULT_THEME_VARIANT!='' && DEFAULT_THEME_VARIANT!='default')
    $bootstrapcss = 'modules/lib_bootstrap/vendor/css/'.DEFAULT_THEME_VARIANT.'/bootstrap.min.css';

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
            array('file'=>$bootstrapcss,),
            array('file'=>'modules/lib_bootstrap/vendor/css/bootstrap-editable.css',),
            array('file'=>'modules/lib_bootstrap/vendor/css/fuelux.min.css',),
            array('file'=>'modules/lib_bootstrap/vendor/css/font-awesome.min.css',),
            array('file'=>'modules/lib_jquery/plugins/jquery.qtip/jquery.qtip.min.css',),
            array('file'=>'modules/lib_jquery/jquery-ui/themes/base/jquery-ui.css',),
            array('file'=>'modules/lib_jquery/plugins/jquery.timepicker/jquery.timepicker.min.css',),
            array('file'=>'modules/lib_jquery/plugins/jquery.datatables/css/dataTables.bootstrap.min.css',),
            array('file'=>'templates/backstrap/css/default/theme.css',),
        ),
        'jquery' => array(
            array(
                'core'    => true,
                'ui'      => true,
                'plugins' => array ('cattranslate','cookie','jquery.mark'),
            )
        ),
        'js' => array(
            array(
                '/modules/lib_jquery/plugins/jquery.datatables/js/jquery.dataTables.min.js',
                '/modules/lib_jquery/plugins/jquery.datatables/js/dataTables.mark.min.js',
                '/modules/lib_jquery/plugins/jquery.datatables/js/dataTables.bootstrap.min.js',
                '/CAT/Backend/js/session.js',
            )
        )
    )
);

if(file_exists(CAT_PATH.'/modules/lib_jquery/jquery-ui/ui/i18n/jquery-ui-i18n.min.js'))
    $mod_headers['backend']['js'][] = 'modules/lib_jquery/jquery-ui/ui/i18n/jquery-ui-i18n.min.js';
