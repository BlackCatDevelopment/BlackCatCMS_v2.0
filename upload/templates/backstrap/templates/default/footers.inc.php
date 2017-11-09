<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2017 Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         backstrap
 *
 */

$mod_footers = array(
    'backend' => array(
        'js' => array(
            '/modules/lib_bootstrap/vendor/v4/js/popper.min.js',
            '/modules/lib_bootstrap/vendor/v4/js/bootstrap.min.js',
            '/modules/lib_bootstrap/vendor/js/bootstrap-editable.min.js',
            //'/modules/lib_bootstrap/vendor/js/bootstrap-filestyle.min.js',
            '/modules/lib_vanillajs/scripts/tippy/1.4.1/tippy.standalone.js',
            '/templates/backstrap/js/bootstrap.growl/growl.min.js',
        )
    )
);

if(CAT_Backend::getArea() == 'media')
{
    $add_js = array(
        // The Load Image plugin is included for the preview images and image resizing functionality
        'modules/lib_jquery/plugins/jquery.fileupload/js/load-image.all.min.js',
        // The Canvas to Blob plugin is included for image resizing functionality
        'modules/lib_jquery/plugins/jquery.fileupload/js/canvas-to-blob.min.js',
        // The Iframe Transport is required for browsers without support for XHR file uploads
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.iframe-transport.js',
        // The basic File Upload plugin
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload.js',
        // The File Upload processing plugin
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-process.js',
        // The File Upload image preview & resize plugin
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-image.js',
        // The File Upload audio preview plugin
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-audio.js',
        // The File Upload video preview plugin
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-video.js',
        // The File Upload validation plugin
        'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-validate.js',
        // The File Upload user interface plugin
        //'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-ui.js',
    );
    $mod_footers['backend']['js'] = array_merge(
        $mod_footers['backend']['js'],
        $add_js
    );
    CAT_Helper_Page::addJS(
        'templates/backstrap/js/load_datatable.js',
        'footer',
        'jquery.dataTables.min.js'
    );
}
