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
            // required by tippy and bootstrap
            '/modules/lib_bootstrap/vendor/v4/js/popper.min.js',
            '/modules/lib_javascript/plugins/tippy/1.4.1/tippy.standalone.js',
            '/modules/lib_bootstrap/vendor/v4/js/bootstrap.min.js',
            '/modules/lib_bootstrap/vendor/js/bootstrap-editable.js',
            '/templates/backstrap/js/bootstrap.growl/growl.min.js',
            // --- aus headers.inc.php verschoben! ---
            'modules/lib_javascript/plugins/jquery.datatables/js/jquery.dataTables.min.js',
            'modules/lib_javascript/plugins/jquery.datatables/js/dataTables.mark.min.js',
            'modules/lib_javascript/plugins/jquery.datatables/js/dataTables.bootstrap4.min.js',
            'modules/lib_javascript/plugins/jquery.fieldset_to_tabs/jquery.fieldset_to_tabs.js',
            'CAT/Backend/js/session.js',
            'templates/backstrap/js/datetimepicker/jquery.datetimepicker.full.js',
            'modules/lib_javascript/plugins/jquery.treed/jquery.treed.js',

        )
    )
);

if(\CAT\Backend::getArea() == 'media')
{
/*
    $add_js = array(
        // The Load Image plugin is included for the preview images and image resizing functionality
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/load-image.all.min.js',
        // The Canvas to Blob plugin is included for image resizing functionality
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/canvas-to-blob.min.js',
        // The Iframe Transport is required for browsers without support for XHR file uploads
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.iframe-transport.js',
        // The basic File Upload plugin
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.fileupload.js',
        // The File Upload processing plugin
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.fileupload-process.js',
        // The File Upload image preview & resize plugin
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.fileupload-image.js',
        // The File Upload audio preview plugin
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.fileupload-audio.js',
        // The File Upload video preview plugin
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.fileupload-video.js',
        // The File Upload validation plugin
        CAT_JS_PLUGINS_PATH.'/jquery.fileupload/js/jquery.fileupload-validate.js',
        // The File Upload user interface plugin
        //'modules/lib_jquery/plugins/jquery.fileupload/js/jquery.fileupload-ui.js',
    );
    $mod_footers['backend']['js'] = array_merge(
        $mod_footers['backend']['js'],
        $add_js
    );
*/
    $am = \CAT\Helper\AssetFactory::getInstance('backend_media');
    $am->addJS(
        'templates/backstrap/js/load_datatable.js',
        'footer',
        'jquery.dataTables.min.js'
    );
}
