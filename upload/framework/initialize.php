
CAT_Registry::register('CAT_CORE', 'Black Cat CMS', true);


// Create database class
// note: we still use the old class here, because there are still some core files
// that use methods like get_one()
require CAT_PATH.'/framework/class.database.php';
$database = new database();

//**************************************************************************
// moved from ./backend/interface/er_levels.php
//**************************************************************************
CAT_Registry::register('ER_LEVELS', array(
    'System Default',
    '6135' => 'E_ALL^E_NOTICE', // standard: E_ALL without E_NOTICE
    '0'    => 'E_NONE',
    '6143' => 'E_ALL',
    '8191' => htmlentities('E_ALL&E_STRICT'), // for programmers
));

//**************************************************************************
//**************************************************************************
$string_file_mode = STRING_FILE_MODE;
CAT_Registry::register('OCTAL_FILE_MODE', (int) octdec($string_file_mode), true);
$string_dir_mode = STRING_DIR_MODE;
CAT_Registry::register('OCTAL_DIR_MODE', (int) octdec($string_dir_mode), true);

//**************************************************************************
// get CAPTCHA and ASP settings
//**************************************************************************
if (!defined('CAT_INSTALL_PROCESS'))
{
    $sql = 'SELECT * FROM `' . CAT_TABLE_PREFIX . 'mod_captcha_control` LIMIT 1';
    if (false !== ($get_settings = $database->query($sql)))
    {
        if ($get_settings->numRows() == 0)
        {
            die("CAPTCHA-Settings not found");
        }
        $setting = $get_settings->fetch(PDO::FETCH_ASSOC);
        CAT_Registry::register('ENABLED_CAPTCHA'    , (($setting['enabled_captcha'] == '1') ? true : false), true);
        CAT_Registry::register('ENABLED_ASP'        , (($setting['enabled_asp'] == '1')     ? true : false), true);
        CAT_Registry::register('CAPTCHA_TYPE'       , $setting['captcha_type']                             , true);
        CAT_Registry::register('ASP_SESSION_MIN_AGE', (int) $setting['asp_session_min_age']                , true);
        CAT_Registry::register('ASP_VIEW_MIN_AGE'   , (int) $setting['asp_view_min_age']                   , true);
        CAT_Registry::register('ASP_INPUT_MIN_AGE'  , (int) $setting['asp_input_min_age']                  , true);
        unset($setting);
    }
}


    
//**************************************************************************
// frontend only
//**************************************************************************
if (!CAT_Backend::isBackend() && !defined('CAT_AJAX_CALL') && !defined('CAT_LOGIN_PHASE') && defined('ENABLE_CSRFMAGIC') && true === ENABLE_CSRFMAGIC )
{
    CAT_Helper_Protect::getInstance()->enableCSRFMagic();
}


    
//**************************************************************************
// set timezone and date/time formats
//**************************************************************************
$timezone_string = (isset($_SESSION['TIMEZONE_STRING']) ? $_SESSION['TIMEZONE_STRING'] : DEFAULT_TIMEZONE_STRING);
date_default_timezone_set($timezone_string);
CAT_Registry::register('CAT_TIME_FORMAT', CAT_Helper_DateTime::getDefaultTimeFormat(), true);
CAT_Registry::register('CAT_DATE_FORMAT', CAT_Helper_DateTime::getDefaultDateFormatShort(), true);
    
//**************************************************************************
// Disable magic_quotes_runtime
//**************************************************************************
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    set_magic_quotes_runtime(0);
	
    
//**************************************************************************
// set the search library
//**************************************************************************
if (!defined('CAT_INSTALL_PROCESS'))
{
    if (false !== ($query = $database->query("SELECT value FROM `:prefix:search` WHERE name='cfg_search_library' LIMIT 1")))
    {
        ($query->rowCount() > 0) ? $res = $query->fetch() : $res['value'] = 'lib_search';
        CAT_Registry::register('SEARCH_LIBRARY', $res['value'], true);
    }
    else
    {
        CAT_Registry::register('SEARCH_LIBRARY', 'lib_search', true);
    }
}
else
{
    CAT_Registry::register('SEARCH_LIBRARY', 'lib_search', true);
}        



//**************************************************************************
// wblib2 autoloader
//**************************************************************************
spl_autoload_register(function($class) {
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
});


