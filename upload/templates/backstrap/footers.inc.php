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

$mod_footers = array(
    'backend' => array(
        'js' => array(
            '/modules/lib_bootstrap/vendor/js/bootstrap.min.js',
            '/modules/lib_bootstrap/vendor/js/bootstrap-editable.min.js',
            '/modules/lib_bootstrap/vendor/js/fuelux.min.js',
            '/modules/lib_jquery/plugins/jquery.timepicker/jquery.timepicker.js',
            '/modules/lib_jquery/plugins/jquery.timepicker/i18n/jquery-ui-timepicker-addon-i18n.min.js',
            '/modules/lib_jquery/plugins/jquery.qtip/jquery.qtip.min.js',
        )
    )
);

if(CAT_Backend::getArea() == 'media')
{
    $mod_footers['backend']['js'][]  = 'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.iframe-transport.js';
    $mod_footers['backend']['js'][]  = 'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload.js';
    CAT_Helper_Page::addJS(
        'templates/backstrap/js/load_datatable.js',
        'footer',
        'jquery.dataTables.min.js'
    );
}
