<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

define('WB2COMPAT',true);

define('WB_SERVER_ADDR', CAT_SERVER_ADDR );
define('WB_PATH', CAT_PATH);
$rel_parsed = parse_url(CAT_URL);
if(!is_array($rel_parsed) || !array_key_exists('scheme',$rel_parsed ) || $rel_parsed['scheme']=='' )
    define('WB_URL', (isset($_SERVER['HTTPS']) ? 'https:' : 'http:') . CAT_URL);
else
define('WB_URL', CAT_URL);
define('ADMIN_PATH', CAT_ADMIN_PATH);
define('ADMIN_URL', CAT_ADMIN_URL);
define('ADMIN_DIRECTORY',CAT_BACKEND_FOLDER);
define('THEME_URL', defined('CAT_THEME_URL') ? CAT_THEME_URL : CAT_URL.'/templates/'.DEFAULT_THEME );
define('THEME_PATH', WB_PATH.'/templates/'.DEFAULT_THEME);
define('LEPTON_SERVER_ADDR', CAT_SERVER_ADDR );
define('LEPTON_PATH', CAT_PATH);
define('LEPTON_URL', WB_URL);
define('TABLE_PREFIX', CAT_TABLE_PREFIX);
define('WB_PREPROCESS_PREG', '/\[wblink([0-9]+)\]/isU' );
define('WBMAILER_DEFAULT_SENDERNAME', CATMAILER_DEFAULT_SENDERNAME );
// define WB_VERSION for backward compatibility
if (!defined('WB_VERSION')) define('WB_VERSION', '2.8.2');
if (!defined('TIMEZONE'))   define('TIMEZONE',DEFAULT_TIMEZONE_STRING);
// load old language file
include CAT_PATH.'/languages/old/'.LANGUAGE.'.php';

global $database, $wb, $admin, $parser;

// -----------------------------------------------------------------------------
// Create new frontend object; this is for backward compatibility only!
include CAT_PATH.'/framework/class.frontend.php';
$wb = new frontend();
// keep SM2 quiet
$wb->extra_where_sql = "visibility != 'none' AND visibility != 'hidden' AND visibility != 'deleted'";
// some modules may use $wb->page_id
if(isset($page_id))
    $wb->page_id=$page_id;
include CAT_PATH.'/framework/frontend.functions.php';
// -----------------------------------------------------------------------------

require_once CAT_PATH.'/framework/class.database.php';
$database = new database();

// old template engine
require_once(CAT_PATH."/include/phplib/template.inc");

// old language definitions - needed for some older modules, like Code2
define('ENABLE_OLD_LANGUAGE_DEFINITIONS',true);

// map new date and time formats to old ones
$wb2compat_format_map = array(
    '%A, %d. %B %Y' => 'l, jS F, Y',
    '%e %B, %Y'     => 'jS F, Y',
    '%d %m %Y'      => 'd M Y',
    '%b %d %Y'      => 'M d Y',
    '%a %b %d, %Y'  => 'D M d, Y',
    '%d-%m-%Y'      => 'd-m-Y',
    '%m-%d-%Y'      => 'm-d-Y',
    '%d.%m.%Y'      => 'd.m.Y',
    '%m.%d.%Y'      => 'm.d.Y',
    '%d/%m/%Y'      => 'd/m/Y',
    '%m/%d/%Y'      => 'm/d/Y',
    '%a, %d %b %Y %H:%M:%S %z' => 'r',
    '%A, %d. %B %Y' => 'l, jS F Y',
    '%H:%M'         => 'H:i',
    '%H:%M:%S'      => 'H:i:s',
    '%I:%M %p'      => 'g:i a',
);

// global settings
if(defined('CAT_DATE_FORMAT') && !defined('DATE_FORMAT') && array_key_exists(CAT_DATE_FORMAT,$wb2compat_format_map))
    define('DATE_FORMAT',$wb2compat_format_map[CAT_DATE_FORMAT]);
if(defined('CAT_DEFAULT_DATE_FORMAT') && !defined('DEFAULT_DATE_FORMAT') && array_key_exists(CAT_DEFAULT_DATE_FORMAT,$wb2compat_format_map))
    define('DEFAULT_DATE_FORMAT',$wb2compat_format_map[CAT_DEFAULT_DATE_FORMAT]);

if(defined('CAT_TIME_FORMAT') && !defined('TIME_FORMAT') && array_key_exists(CAT_TIME_FORMAT,$wb2compat_format_map))
    define('TIME_FORMAT',$wb2compat_format_map[CAT_TIME_FORMAT]);
if(defined('CAT_DEFAULT_TIME_FORMAT') && !defined('DEFAULT_TIME_FORMAT') && array_key_exists(CAT_DEFAULT_TIME_FORMAT,$wb2compat_format_map))
    define('DEFAULT_TIME_FORMAT',$wb2compat_format_map[CAT_DEFAULT_TIME_FORMAT]);

CAT_Registry::set('WB2COMPAT_FORMAT_MAP',$wb2compat_format_map);

if(!function_exists('show_menu'))
{
    function show_menu()
    {
        return show_menu2();
    }
}


// This is for old language strings
global $HEADING, $TEXT, $MESSAGE;
foreach(array('TEXT', 'HEADING', 'MESSAGE' ) as $global)
{
    if(isset(${$global}) && is_array(${$global}))
    {
        $parser->setGlobals($global, ${$global});
    }
}

