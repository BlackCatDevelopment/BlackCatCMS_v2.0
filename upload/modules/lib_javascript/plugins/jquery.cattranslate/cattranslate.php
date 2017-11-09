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

$val  = CAT_Helper_Validate::getInstance();
$attr = $val->get('_REQUEST','attr');
$msg  = $val->get('_REQUEST','msg');
$mod  = $val->get('_REQUEST','mod');

if( version_compare(phpversion(),'5.4','<') )
{
    $msg  = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    $attr = htmlspecialchars($attr, ENT_QUOTES, 'UTF-8');
}
else
{
    $msg  = htmlspecialchars($msg, ENT_XHTML, 'UTF-8');
    $attr = htmlspecialchars($attr, ENT_XHTML, 'UTF-8');
}

if(CAT_Backend::isBackend() || $mod = 'BE')
{
    $h   = CAT_Backend::getInstance();
    $mod = NULL;
} else {
    $h   = CAT_Frontend::getInstance();
}

if($mod)
{
    $paths = array(
        CAT_ENGINE_PATH.'/modules/'.$mod.'/languages',
        CAT_ENGINE_PATH.'/templates/'.$mod.'/languages',
    );
    $lang = strtoupper($h->lang()->getLang());
    foreach(array_values($paths) as $dir)
    {
        if(file_exists($dir.'/'.$lang.'.php'))
        {
            $h->lang()->addFile($lang,$dir);
        }
    }
}

if(is_object($h)) {
	echo '<data>'.$h->lang()->translate($msg,$attr).'</data>';
}
else {
	echo '<data>'.$msg.'</data>';
}