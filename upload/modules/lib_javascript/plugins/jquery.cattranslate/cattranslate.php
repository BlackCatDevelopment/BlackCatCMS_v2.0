<?php

/**
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_jquery
 *
 */

$val  = \CAT\Helper\Validate::getInstance();
$attr = $val->get('_REQUEST','attr');
$msg  = $val->get('_REQUEST','msg');
$mod  = $val->get('_REQUEST','mod');

if( version_compare(phpversion(),'5.4','<') )
{
    $msg  = htmlspecialchars(urldecode($msg), ENT_QUOTES, 'UTF-8');
    $attr = htmlspecialchars($attr, ENT_QUOTES, 'UTF-8');
}
else
{
    $msg  = htmlspecialchars(urldecode($msg), ENT_XHTML, 'UTF-8');
    $attr = htmlspecialchars($attr, ENT_XHTML, 'UTF-8');
}

if(\CAT\Backend::isBackend() || $mod = 'BE')
{
    $mod = NULL;
}

if($mod)
{
    $paths = array(
        CAT_ENGINE_PATH.'/modules/'.$mod.'/languages',
        CAT_ENGINE_PATH.'/templates/'.$mod.'/languages',
    );
    $lang = strtoupper(\CAT\Base::lang()->getLang());
    foreach(array_values($paths) as $dir)
    {
        if(file_exists($dir.'/'.$lang.'.php'))
        {
            \CAT\Base::lang()->addFile($lang,$dir);
        }
    }
}

echo '<data>'.\CAT\Base::lang()->t($msg).'</data>';
