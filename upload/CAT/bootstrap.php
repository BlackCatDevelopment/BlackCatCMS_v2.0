<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2017 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/


if(!defined('CAT_ENGINE_PATH')) die;

define('CAT_ADMIN_URL',CAT_URL.'/'.CAT_BACKEND_PATH);

// Composer autoloader
require __DIR__ . '/vendor/autoload.php';

// we require UTF-8
ini_set('default_charset','UTF-8');


//******************************************************************************
// register autoloader
//******************************************************************************
spl_autoload_register(function($class)
{
    if(!substr_compare($class, 'wblib', 0, 4)) // wblib2 components
    {
        $file = str_replace(
            '\\',
            '/',
            CAT_Helper_Directory::sanitizePath(
                CAT_ENGINE_PATH.'/modules/lib_wblib/'.str_replace(
                    array('\\','_'),
                    array('/','/'),
                    $class
                ).'.php'
            )
        );
        if (file_exists($file))
            @require $file;
    }
    else                                       // BC components
    {
        $file = '/'.str_replace('_', '/', $class);
        $file = CAT_ENGINE_PATH.'/'.$file.'.php';
        if (file_exists($file))
            @require_once $file;
    }
    // next in stack
});

//******************************************************************************
// Register Whoops as Exception handler
//******************************************************************************
if(CAT_Backend::isBackend())
{
    #$whoops = new \Whoops\Run;
    #$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    #$whoops->register();
}

//******************************************************************************
// Get website settings and register as globals
//******************************************************************************
CAT_Object::loadSettings();
if(!CAT_Registry::exists('LANGUAGE') && CAT_Registry::exists('DEFAULT_LANGUAGE'))
{
    CAT_Registry::register('LANGUAGE',CAT_Registry::get('DEFAULT_LANGUAGE'),true);
}

//******************************************************************************
// Set theme
//******************************************************************************
CAT_Registry::register('CAT_THEME_PATH'  ,CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME')   , true);
CAT_Registry::register('CAT_TEMPLATE_DIR',CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('DEFAULT_TEMPLATE'), true);

//******************************************************************************
// Set as constants for simpler use
//******************************************************************************
CAT_Registry::register('CAT_VERSION'     ,CAT_Registry::get('CAT_VERSION')                                   , true);

//******************************************************************************
// Start a session
//******************************************************************************
if (!defined('SESSION_STARTED'))
{
    session_name(CAT_Registry::get('APP_NAME').'sessionid');
	$cookie_settings = session_get_cookie_params();
	session_start();
    // extend the session lifetime on each action
    setcookie(
        session_name(),
        session_id(),
        time()+ini_get('session.gc_maxlifetime'),
        $cookie_settings["path"],
        $cookie_settings["domain"],
        (strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) === 'https'),
        true
    );
    CAT_Registry::register('SESSION_STARTED', true, true);
}
if (defined('ENABLED_ASP') && ENABLED_ASP && !isset($_SESSION['session_started']))
    $_SESSION['session_started'] = time();

//******************************************************************************
// register some globals
//******************************************************************************
$parser = CAT_Object::tpl();
