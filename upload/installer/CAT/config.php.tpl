<?php
if(defined('CAT_PATH')) {
    die('By security reasons it is not permitted to load \'config.php\' twice! Forbidden call from \''.$_SERVER['SCRIPT_NAME'].'\'!');
}
// *****************************************************************************
// please set the path names for the backend subfolders here; that is,
// if you rename 'backend' to 'myadmin', for example, set 'CAT_BACKEND_FOLDER'
// to 'myadmin'.
// *****************************************************************************
// path to backend subfolder; default name is 'backend'
define('CAT_BACKEND_FOLDER', 'backend');
// *****************************************************************************
define('CAT_BACKEND_PATH', CAT_BACKEND_FOLDER );
define('CAT_TABLE_PREFIX', '%%prefix%%');
define('CAT_SERVER_ADDR', '%%ipaddress%%');
define('CAT_PATH', %%path%%);
define('CAT_URL', '%%url%%');
define('CAT_ADMIN_PATH', CAT_PATH.'/'.CAT_BACKEND_PATH);
define('CAT_ADMIN_URL', CAT_URL.'/'.CAT_BACKEND_PATH);

// if you have problems with SSL, set this to 'false' or delete the following line
define('CAT_BACKEND_REQ_SSL', %%ssl%%);
define('ALLOW_SHORT_PASSWORDS', true);

if (!defined('CAT_INSTALL')) require_once(CAT_PATH.'/CAT/bootstrap.php');

if(defined('WB2COMPAT') && file_exists(CAT_PATH.'/framework/wb2compat.php'))
{
    include_once CAT_PATH.'/framework/wb2compat.php';
}

