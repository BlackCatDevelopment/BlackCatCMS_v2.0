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
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

global $_be_mem, $_be_time;
$_be_time = microtime(TRUE);
$_be_mem  = memory_get_usage();

if (!class_exists('CAT_Backend', false))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/Object.php';
    }

    class CAT_Backend extends CAT_Object
    {

        protected      $_config  = array( 'loglevel' => 7 );
        private static $instance = array();
        private static $form     = NULL;
        private static $route    = NULL;
        private static $params   = NULL;

        public static function getInstance($section_name='Start',$section_permission='start',$auto_header=true,$auto_auth=true)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                self::$instance->tpl()->setGlobals(array(
                    'meta' => array(
                        'LANGUAGE' => strtolower(LANGUAGE),
                        'CHARSET'  => (defined('DEFAULT_CHARSET')) ? DEFAULT_CHARSET : "utf-8",
                    ),
                ));
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * dispatch backend route
         **/
        public static function dispatch()
        {
            $self   = self::getInstance();

            // no route yet
            if(!self::$route)
            {
                self::$route
                    = isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] :
                      isset($_SERVER['PATH_INFO'])      ? $_SERVER['PATH_INFO']      :
                      isset($_SERVER['REQUEST_URI'])    ? $_SERVER['REQUEST_URI']    : '/'
                    ;
                $path_prefix = str_ireplace(
                    CAT_Helper_Directory::sanitizePath($_SERVER['DOCUMENT_ROOT']),
                    '',
                    CAT_Helper_Directory::sanitizePath(CAT_PATH)
                );
                $self->log()->logDebug(sprintf(
                    'document root [%s], CAT_PATH [%s], current route [%s], route prefix (rel. path to doc root) [%s]',
                    $_SERVER['DOCUMENT_ROOT'], CAT_Helper_Directory::sanitizePath(CAT_PATH), self::$route, $path_prefix
                ));
                self::$route = str_ireplace(
                    CAT_Helper_Directory::sanitizePath($path_prefix.'/'.CAT_BACKEND_FOLDER),
                    '',
                    self::$route
                );
                // remove leading /
                if(!strpos(self::$route,'/',0))
                    self::$route = substr(self::$route,1,strlen(self::$route));
            }
            $self->log()->logDebug(sprintf('resulting route [%s]',self::$route));

            // handle the route
            // special cases: login. logout and authenticate
            if(preg_match('~^(login|authenticate|logout)~',self::$route,$m))
            {
                $func = $m[1];
                $self->log()->logDebug(sprintf('calling func [%s]',$func));
                return self::$func();
            }
            else
            {
                // any other routes require user login
                if(!$self->user()->is_authenticated())
                {
                    // re-route
                    header('Location: '.CAT_ADMIN_URL.'/login');
                }
                else
                {
                    // the user is logged in, resolve the route
                    $route      = explode('/',str_replace('\\','/',self::$route));
                    // first part of the route is the controller name
                    $controller = array_shift($route);
                    // second item (if exists) is the function name; if
                    // no function name is available, use index()
                    $function   = (count($route) ? array_shift($route) : 'index');
                    // if there are any items left, save as params
                    if(count($route)) self::$params = $route;
                    // set user data as template var {$USER.<property>}
                    $self->tpl()->setGlobals('USER',$self->user()->get());
                    $self->tpl()->setGlobals('SECTION',ucfirst($controller));
                    // check the user permissions
                    if(!$self->user()->hasPerm($controller))
                    {
                        CAT_Object::printFatalError('Access denied');
                    }
                    // controller class name
                    $class   = 'CAT_Backend_'.ucfirst($controller);
                    // add perms for use inside the templates
                    $self->tpl()->setGlobals('PERMS',CAT_User::getInstance()->getPerms());
                    // check if the controller exists
                    try
                    {
                        $handler = $class::getInstance();
                        if(!$self->user()->hasPerm($function))
                        {
                            CAT_Object::printFatalError('Access denied');
                        }
                        if(method_exists($handler,$function))
                        {
                            return $handler::$function();
                            exit;
                        }
                    }
                    catch( Exception $e )
                    {
                        if(method_exists($self,$function))
                        {
                            return self::$function();
                            exit;
                        }
                    }
                }
            }
            #echo "ROUTE: ", self::$route;
            #ROUTE: /blackcat/bcwa20/backend/start/index.php
        }   // end function dispatch()

        /**
         * initializes template search paths for backend
         *
         * @access public
         * @return
         **/
        public static function initPaths()
        {
            $self = self::getInstance();
            $self->tpl()->setPath(CAT_THEME_PATH.'/templates/default','backend');
            $self->tpl()->setFallbackPath(CAT_THEME_PATH.'/templates/default','backend');

            if(file_exists(CAT_THEME_PATH.'/templates/default'))
            {
                if(!CAT_Registry::exists('DEFAULT_THEME_VARIANT') || CAT_Registry::get('DEFAULT_THEME_VARIANT') == '')
                {
                    CAT_Registry::set('DEFAULT_THEME_VARIANT','default');
                    $self->tpl()->setGlobals('DEFAULT_THEME_VARIANT','default');
                }
            }
            if(CAT_Registry::get('DEFAULT_THEME_VARIANT') != '' && file_exists(CAT_THEME_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME_VARIANT')))
            {
                $self->tpl()->setPath(CAT_THEME_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME_VARIANT'),'backend');
            }
        }   // end function initPaths()

        /**
         * checks if the current path is inside the backend folder
         *
         * @access public
         * @return boolean
         **/
        public static function isBackend()
        {
            $url = CAT_Helper_Validate::sanitizeServer('SCRIPT_NAME');
            if ( preg_match( '~/'.CAT_BACKEND_FOLDER.'/~i', $url ) )
                return true;
            else
                return false;
        }   // end function isBackend()

        /**
         *
         * @access public
         * @return
         **/
        public static function getForms($section)
        {
            if(!self::$form)
            {
                $init = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME').'/forms.init.php');
                if(file_exists($init))
                    require $init;
                self::$form = \wblib\wbForms::getInstance();
                self::$form->set('wblib_url',CAT_URL.'/modules/lib_wblib/wblib');
                self::$form->set('lang_path',CAT_PATH.'/languages');
            }
            //P:\apache\htdocs\blackcat\bcwa20\CAT\Forms\settings
            if(file_exists(CAT_PATH.'/CAT/Forms/'.$section.'/inc.forms.php'))
                self::$form->loadFile('inc.forms.php',CAT_PATH.'/CAT/Forms/'.$section);
            return self::$form;
        }   // end function getForms()

        /**
         * get the main menu (backend sections)
         * checks the user priviledges
         *
         * @access public
         * @return array
         **/
        public static function getMainMenu($current=NULL)
        {
            $menu = array();
            $self = self::getInstance();

            if(!$current) $current = self::$route;

            foreach(array_values(array('dashboard','pages','media','settings','addons','admintools','users','groups','roles','preferences')) as $item)
            {
                if($self->user()->hasPerm($item))
                {
                    $menu[] = array(
                        'link'    => CAT_ADMIN_URL.'/'.$item,
                        'title'   => $self->lang()->translate(ucfirst($item)),
                        'name'    => $item,
                        'current' => ( $current && $current == $item ) ? true : false,
                    );
                }
            }
            return $menu;
        }

        /**
         *
         * @access public
         * @return
         **/
        public function getRouteParams()
        {
            return self::$params;
        }   // end function getRouteParams()

        /**
         *  Print the admin header
         *
         *  @access public
         *  @return void
         */
        public static function print_header()
        {
            $tpl_data = array();
            $addons   = CAT_Helper_Addons::getInstance();
            // init template search paths
            self::initPaths();
            $tpl_data['MAIN_MENU'] = self::getMainMenu();
            self::getInstance()->tpl()->output('header', $tpl_data);
        }   // end function print_header()

        /**
        * Print the admin footer
        *
        * @access public
        **/
        public static function print_footer()
        {
            $data = array();
            self::initPaths();

            $t = ini_get('session.gc_maxlifetime');
            $data['SESSION_TIME'] = sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);

            $self = self::getInstance();

            // ========================================================================
            // ! Try to get the actual version of the backend-theme from the database
            // ========================================================================
            $backend_theme_version = '-';
            if (defined('DEFAULT_THEME'))
            {
                $backend_theme_version
                    = $self->db()->query(
                          "SELECT `version` from `:prefix:addons` where `directory`=:theme",
                          array('theme'=>DEFAULT_THEME)
                      )->fetchColumn();
            }
            $data['THEME_VERSION'] = $backend_theme_version;
            $data['THEME_NAME']    = ucfirst(DEFAULT_THEME);

            global $_be_mem, $_be_time;
            $data['system_information'] = array(
                array(
                    'name'      => $self->lang()->translate('PHP version'),
                    'status'    => phpversion(),
                ),
                array(
                    'name'      => $self->lang()->translate('Memory usage'),
                    'status'    => '~ ' . sprintf('%0.2f',( (memory_get_usage() - $_be_mem) / (1024 * 1024) )) . ' MB'
                ),
                array(
                    'name'      => $self->lang()->translate('Script run time'),
                    'status'    => '~ ' . sprintf('%0.2f',( microtime(TRUE) - $_be_time )) . ' sec'
                ),
            );

            $self->tpl()->output('footer', $data);

            // ======================================
            // ! make sure to flush the output buffer
            // ======================================
            if(ob_get_level()>1)
                while (ob_get_level() > 0)
                    ob_end_flush();

        }   // end function print_footer()

// =============================================================================
//     Route handler
// =============================================================================

        /**
         * handle user authentication
         *
         * @access public
         * @return mixed
         **/
        public static function authenticate()
        {
            $self = self::getInstance();
            if($self->user()->authenticate() === true)
            {
                $self->log()->logDebug('Authentication succeeded');
                $_SESSION['USER_ID'] = $self->user()->get('user_id');
                // forward
                echo json_encode(array(
                    'success' => true,
                    'url'     => CAT_ADMIN_URL.'/dashboard'
                ));
                exit;
            }
            $self->log()->logDebug('Authentication failed!');
            header('Location: '.CAT_ADMIN_URL.'/login');
            exit;
        }   // end function authenticate()

        /**
         * show the login page
         *
         * @access public
         * @return
         **/
        public static function login()
        {
            global $parser;
            // we need this twice
            $username_fieldname = CAT_Helper_Validate::createFieldname('username_');
            // for debugging
            $self = self::getInstance();
			$tpl_data = array(
                'USERNAME_FIELDNAME'    => $username_fieldname,
                'PASSWORD_FIELDNAME'    => CAT_Helper_Validate::createFieldname('password_'),
                'USERNAME'              => CAT_Helper_Validate::sanitizePost($username_fieldname),
            );

            $self->log()->logDebug('printing login page');
            $parser->output('login',$tpl_data);
        }   // end function login()

        /**
         *
         * @access public
         * @return
         **/
        public static function logout()
        {
            $self = self::getInstance();
            $self->user()->logout();
        }
    }
}