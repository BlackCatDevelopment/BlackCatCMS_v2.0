<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         backstrap
 *
 */

$pg = \CAT\Helper\Page::getInstance();

$bootstrapcss = 'modules/lib_bootstrap/vendor/v4/css/default/bootstrap.min.css';
$variant      = \CAT\Registry::get('DEFAULT_THEME_VARIANT');
if($variant!='' && $variant!='default')
    $bootstrapcss = 'modules/lib_bootstrap/vendor/v4/css/'.$variant.'/bootstrap.min.css';

$mod_headers = array(
    'backend' => array(
        'meta' => array(
            array( 'charset' => (defined('DEFAULT_CHARSET') ? DEFAULT_CHARSET : "utf-8") ),
            array( 'http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge' ),
            array( 'name' => 'viewport', 'content' => 'width=device-width, initial-scale=1' ),
            array( 'name' => 'description', 'content' => 'BlackCat CMS - '.$pg->lang()->translate('Administration') ),
        ),
        'css' => array(
            array('file'=>'modules/lib_bootstrap/vendor/css/font-awesome.min.css',),
            array('file'=>$bootstrapcss,),
            array('file'=>'modules/lib_javascript/plugins/tippy/1.4.1/tippy.css'),
            array('file'=>'modules/lib_javascript/jquery-ui/themes/base/jquery-ui.css',),
            array('file'=>'templates/backstrap/js/datetimepicker/jquery.datetimepicker.min.css',),
            array('file'=>'modules/lib_javascript/plugins/jquery.datatables/css/dataTables.bootstrap4.min.css',),
            array('file'=>'templates/backstrap/js/bootstrap4-editable/css/bootstrap-editable.css',),
            array('file'=>'templates/backstrap/css/default/theme.css',),
            array('file'=>'templates/backstrap/css/default/sidebar.css',),
        ),
        'jquery' => array(
            'core'    => true,
            'ui'      => true,
            'plugins' => array ('jquery.cattranslate','jquery.cookies','jquery.mark'),
        ),
        'js' => array(
            array(
                'conditional' => 'lt IE 9',
                'files' => array(
                    'https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js',
                    'https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js',
                ),
            ),
        )
    )
);

if(file_exists(CAT_JQUERY_PATH.'/jquery-ui/ui/i18n/jquery-ui-i18n.min.js'))
    $mod_headers['backend']['js'][] = 'modules/lib_javascript/jquery-ui/ui/i18n/jquery-ui-i18n.min.js';

if(\CAT\Backend::getArea() == 'media')
{
    $mod_headers['backend']['css'][] = array('file'=>'templates/backstrap/css/default/ekko-lightbox.css');
    $mod_headers['backend']['js'][]  = 'templates/backstrap/js/bootstrap.lightbox/ekko-lightbox.min.js';
    $mod_headers['backend']['css'][] = array('file'=>'modules/lib_javascript/plugins/jquery.fileupload/css/jquery.fileupload-ui.css');
    $mod_headers['backend']['js'][]  = 'modules/lib_javascript/plugins/jquery.datatables/js/jquery.dataTables.min.js';
}

if(\CAT\Backend::getArea() == 'admintools')
{
    $mod_headers['backend']['js'][] = 'templates/backstrap/js/dashboard.js';
}

if(\CAT\Backend::getArea() == 'roles')
{
    $mod_headers['backend']['css'][] = array('file'=>'modules/lib_javascript/plugins/jquery.fancytree/skin-lion/ui.fancytree.min.css');
}