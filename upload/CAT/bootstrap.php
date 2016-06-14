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
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016 Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/CAT/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/CAT/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/CAT/class.secure.php')) {
		include($root.'/CAT/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

ini_set('default_charset','UTF-8');

//**************************************************************************
// register autoloader
//**************************************************************************
spl_autoload_register(function($class)
{
    if (defined('CAT_PATH'))
    {
        if(!substr_compare($class, 'wblib', 0, 4))
        {
            if(substr_count($class,'wbForms') && !class_exists('\wblib\wbForms'))
            {
                @require CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_wblib/wblib/wbForms.php');
            }
            else
            {
                $file = str_replace('\\','/',CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/lib_wblib/'.str_replace(array('\\','_'), array('/','/'), $class).'.php'));
                if (file_exists($file)) {
                    @require $file;
                }
            }
        }
        else
        {
            $file = '/'.str_replace('_', '/', $class); // files in CAT subfolder
            // files in (old) framework subfolder (do not have CAT_ in class name)
            if(substr_compare($class, 'CAT_', 0, 4))
                $file = '/framework'.$file;
            if (file_exists(CAT_PATH . $file . '.php'))
                @require CAT_PATH . $file . '.php';
        }
    }
    // next in stack
});

CAT_Registry::register('URL_HELP', 'http://blackcat-cms.org/', true);
CAT_Registry::register('IS_WIN'  , (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false, true);

//**************************************************************************
// Get website settings (title, keywords, description, header, and footer)
//**************************************************************************
$sql = 'SELECT `name`, `value` FROM `:prefix:settings` ORDER BY `name`';
if (($result = CAT_Helper_DB::getInstance()->query($sql)) && ($result->rowCount() > 0))
{
    while (false != ($row = $result->fetch()))
    {
        if (preg_match('/^[0-7]{1,4}$/', $row['value']) == true)
            $value = $row['value'];
        elseif (preg_match('/^[0-9]+$/S', $row['value']) == true)
            $value = intval($row['value']);
        elseif ($row['value'] == 'false')
            $value = false;
        elseif ($row['value'] == 'true')
            $value = true;
        else
            $value = $row['value'];
        $temp_name = strtoupper($row['name']);
        CAT_Registry::register($temp_name, $value, true, true);
    }
    unset($row);
}
else
{
    CAT_Object::printFatalError("No settings found in the database, please check your installation!");
}

//**************************************************************************
// Start a session
//**************************************************************************
if (!defined('SESSION_STARTED'))
{
    session_name(APP_NAME.'sessionid');
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

//**************************************************************************
// Get users language
//**************************************************************************
$val = CAT_Helper_Validate::getInstance();

// user selection
if($val->get('_REQUEST','lang'))
{
    $language = strtoupper($val->get('_REQUEST','lang'));
    $language = $val->lang()->checkLang($language)
              ? $language
              : CAT_Registry::get('DEFAULT_LANGUAGE');
    $_SESSION['lang'] = $language;
    CAT_Registry::register('LANGUAGE', strtoupper($language), true);
}

if ( ! CAT_Registry::exists('LANGUAGE') )
    CAT_Registry::register('LANGUAGE',DEFAULT_LANGUAGE,true);

// Load Language file
#if (!defined('LANGUAGE_LOADED'))
#    if (!file_exists(CAT_PATH . '/languages/' . LANGUAGE . '.php'))
#        exit('Error loading language file ' . LANGUAGE . ', please check configuration');
#    else
#        require_once(CAT_PATH . '/languages/' . LANGUAGE . '.php');

//**************************************************************************
// Set theme
//**************************************************************************
CAT_Registry::register('CAT_THEME_URL' , CAT_URL  . '/templates/' . DEFAULT_THEME, true);
CAT_Registry::register('CAT_THEME_PATH', CAT_PATH . '/templates/' . DEFAULT_THEME, true);

//**************************************************************************
// get template engine
//**************************************************************************
global $parser;
$parser = CAT_Helper_Template::getInstance('Dwoo');

// set template path
if(CAT_Backend::isBackend())
{
    $parser->setPath(CAT_THEME_PATH . '/templates');
    $parser->setFallbackPath(CAT_THEME_PATH . '/templates');
    $parser->setGlobals('DEFAULT_THEME_VARIANT', (DEFAULT_THEME_VARIANT!='' ? DEFAULT_THEME_VARIANT : 'default' ));
}
else
{
}









CAT_Registry::register('CAT_INITIALIZED', true, true);