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

if (!class_exists('CAT_Backend', false))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/Object.php';
    }

    class CAT_Backend extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        #protected static $loglevel = \Monolog\Logger::DEBUG;

        private   static $instance = array();
        private   static $form     = NULL;
        private   static $route    = NULL;
        private   static $params   = NULL;
        private   static $menu     = NULL;

        // public routes (do not check for authentication)
        private   static $public   = array(
            'languages','login','authenticate','logout','qr','tfa'
        );

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                self::$instance->tpl()->setGlobals(array(
                    'LANGUAGE'      => strtolower(CAT_Registry::get('language',NULL,self::$instance->lang()->getLang())),
                    'CHARSET'       => CAT_Registry::exists('default_charset') ? CAT_Registry::get('default_charset') : "utf-8",
                    'CAT_ADMIN_URL' => CAT_ADMIN_URL,
                    'WEBSITE_TITLE' => CAT_Registry::get('WEBSITE_TITLE'),
                ));
                if(self::$instance->user()->is_authenticated())
                {
                    // for re-login dialog
                    self::$instance->tpl()->setGlobals(array(
                        'PASSWORD_FIELDNAME' => CAT_Helper_Validate::createFieldname('password_'),
                        'USERNAME_FIELDNAME' => CAT_Helper_Validate::createFieldname('user_'),
                    ));
                }
                self::$instance->initPaths();
                $current_language = strtoupper(CAT_Registry::get('language',NULL,self::$instance->lang()->getLang()));
                self::$instance->lang()->addFile(
                    $current_language,
                    dirname(__FILE__).'/Backend/languages/'
                );
                if(file_exists(CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/languages/'.$current_language.'.php'))
                {
                    self::$instance->lang()->addFile(
                        $current_language,
                        CAT_ENGINE_PATH.'/templates/'.CAT_Registry::get('default_theme').'/languages/'
                    );
                }
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * dispatch backend route
         **/
        public static function dispatch()
        {
            $self   = self::getInstance();
            // get the route handler
            $router = self::router();
            $self->log()->addDebug('checking if route is protected');
            // check for protected route
            if(!in_array($router->getFunction(),self::$public))
            {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Das erfordert die Einhaltung bestimmter Regeln, z.B. dass die Funktion
// "index" immer das Recht "<Funktionsname>" erfordert (z.B. "groups"), alle
// weiteren das Recht "<Funktionsname>_<$funcname>" (z.B. "pages_list")
// Der Code ist irgendwie unelegant... Später nochmal drauf schauen
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                $need_perm = strtolower($router->getFunction());
                $router->setController('CAT_Backend_'.ucFirst($router->getFunction()));
                $router->setFunction(
                    ( ($funcname = $router->getParam(0,true)) !== NULL ? $funcname : 'index' )
                );
                if($funcname)
                    $need_perm .= '_'.strtolower($funcname);
                $router->protect($need_perm);
            }

            // re-route to login page if the route is protected and the user is
            // not logged in
            if($router->isProtected() && !$self->user()->is_authenticated())
            {
                header('Location: '.CAT_ADMIN_URL.'/login');
            }
            else
            {
                // save current route for later use
                self::$route = $router->getRoute();

                // if asJSON() is true, nothing will be rendered, so we don't
                // need this
                if(!self::asJSON())
                {
                    // set some template globals
                    $self->tpl()->setGlobals(
                        array(
                            'meta' => array(
                                'USER'    => $self->user()->get(),
                                'SECTION' => ucfirst(str_replace('CAT_Backend_','',$router->getController())),
                                'PERMS'   => CAT_User::getInstance()->getPerms()
                            )
                        )
                    );
                    if($router->getFunction() !== 'index') {
                        $self->tpl()->setGlobals(
                            array(
                                'meta' => array(
                                    'ACTION'  => ucfirst($router->getFunction()),
                                )
                            )
                        );
                    }

                    // pages list
                    if($self->user()->hasPerm('pages_list'))
                    {
                        $self->tpl()->setGlobals('pages',CAT_Backend_Page::tree());
                        $self->tpl()->setGlobals('sections',CAT_Sections::getSections());
                    }
                }

                // finally, dispatch the request (call controller)
                $router->dispatch();
            }
        }   // end function dispatch()

        /**
         *
         * @access public
         * @return
         **/
        public static function getArea()
        {
            $route = self::router()->getRoute();
            // example route: backend/page/edit/1
            $parts = explode('/',$route);
            return $parts[1];
        }   // end function getArea()

        /**
         * get the main menu (backend sections)
         * checks the user priviledges
         *
         * @access public
         * @return array
         **/
        public static function getMainMenu($parent=NULL)
        {
            if(!self::$menu)
            {
                $self = CAT_Backend::getInstance();
                // get backend areas
                $r    = $self->db()->query('SELECT * FROM `:prefix:backend_areas` ORDER BY `parent`,`position`');
                self::$menu = $r->fetchAll(\PDO::FETCH_ASSOC);
                $self->log()->addDebug('main menu items from DB: '.print_r(self::$menu,1));
                foreach(self::$menu as $i => $item)
                {
                    self::$menu[$i]['title'] = $self->lang()->t(ucfirst($item['name']));
                    if($item['controller'] != '') # find controller
                    {
                        self::$menu[$i]['href']
                            = CAT_ADMIN_URL.'/'
                            . ( strlen($item['controller']) ? $item['controller'].'/' : '' )
                            . $item['name'];
                    }
                    else
                    {
                        self::$menu[$i]['href'] = CAT_ADMIN_URL.'/'.$item['name'];
                    }
                    self::$menu[$i]['controller'] = ( isset($item['controller']) ? $item['controller'] : $item['name'] );
                    if(preg_match('~'.$item['name'].'$~i',self::$route))
                    {
                        self::$menu[$i]['is_current'] = 1;
                        $parents = explode('/',$item['trail']);
                        foreach(array_values($parents) as $pid)
                        {
                            $path = CAT_Helper_Array::search($pid,self::$menu,'id');
                            self::$menu[$path[0]]['is_in_trail'] = 1;
                        }
                    }
                }

/*
            [id] => 1
            [name] => dashboard
            [position] => 1
            [parent] => 0
            [controller] =>
            [level] => 0
            [trail] => 1
            [title] => Dashboard
            [href] => https://localhost/backend/dashboard
            [is_current] => 1
            [is_in_trail] => 1
*/


                // get available settings categories / regions
                $r       = $self->db()->query('SELECT `region` FROM `:prefix:settings` GROUP BY `region`');
                $regions = $r->fetchAll();
                $path    = CAT_Helper_Array::search('settings',self::$menu,'name');
                $id      = 1000;
                $set_parent = self::$menu[$path[0]];
                foreach($regions as $region)
                {
                    self::$menu[] = array(
                        'id'          => $id,
                        'name'        => $region['region'],
                        'parent'      => $set_parent['id'],
                        'title'       => self::humanize($region['region']),
                        'level'       => ($set_parent['level']+1),
                        'href'        => CAT_ADMIN_URL.'/settings/'.$region['region'],
                        'is_current'  => false,
                        'is_in_trail' => false,
                        'trail'       => $set_parent['trail'].'/'.$id,
                    );
                    $id++;
                }
            }

            if($parent)
            {
                $menu = self::$menu;
                $menu = CAT_Helper_Array::filter($menu,'parent',$parent);
                return $menu;
            }

            return self::$menu;
        }   // end function getMainMenu()

        /**
         * create a global FormBuilder handler
         *
         * @access public
         * @return
         **/
        public static function initForm()
        {
            if(!self::$form)
            {
                self::$form = CAT_Object::form();
                \wblib\wbFormsJQuery::set('enabled',false);
                \wblib\wbFormsJQuery::set('load_ui_theme',false);
                \wblib\wbFormsJQuery::set('disable_tooltips',true);
            }
            return self::$form;
        }   // end function initForm()

        /**
         * initializes template search paths for backend
         *
         * @access public
         * @return
         **/
        public static function initPaths()
        {
            $self    = self::getInstance();
            $theme   = CAT_Registry::get('default_theme');
            $variant = CAT_Registry::get('default_theme_variant');

            if(!$variant || !strlen($variant)) $variant = 'default';

            $self->tpl()->setPath(CAT_ENGINE_PATH.'/templates/'.$theme.'/templates/'.$variant,'backend');
            $self->tpl()->setFallbackPath(CAT_ENGINE_PATH.'/templates/'.$theme.'/templates/default','backend');

        }   // end function initPaths()

        /**
         * checks if the current path is inside the backend folder
         *
         * @access public
         * @return boolean
         **/
        public static function isBackend()
        {
            $current_route = CAT_Object::router()->getRoute();
            $backend_route = defined('CAT_BACKEND_PATH')
                           ? CAT_BACKEND_PATH
                           : 'backend';
//echo "curr $current_route be $backend_route<br />";

            if(preg_match('~^/?'.$backend_route.'/~i', $current_route))
                return true;
            else
                return false;
        }   // end function isBackend()


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
            if(self::user()->login() === true)
            {
                self::log()->addDebug(sprintf(
                    'Authentication succeeded, username [%s], id [%s]',
                    self::user()->get('username'), self::user()->get('user_id')
                ));
                $_SESSION['USER_ID'] = self::user()->get('user_id');
                // forward
                if(self::asJSON())
                {
                    self::log()->addDebug(sprintf(
                        'sending json result, forward to URL [%s]',
                        CAT_ADMIN_URL.'/dashboard'
                    ));
                    CAT_Helper_JSON::printData(array(
                        'success' => true,
                        'url'     => CAT_ADMIN_URL.'/dashboard'
                    ));
                }
                else
                {
                    self::log()->addDebug(sprintf(
                        'forwarding to URL [%s]',
                        CAT_ADMIN_URL.'/dashboard'
                    ));
                    header('Location: '.CAT_ADMIN_URL.'/dashboard');
                }
            }
            else
            {
                self::log()->addDebug('Authentication failed!');
                if(self::asJSON())
                    CAT_Helper_JSON::printError('Authentication failed!');
                else
                    self::printFatalError('Authentication failed!');
            }
            exit;
        }   // end function authenticate()

        /**
         *
         * @access public
         * @return
         **/
        public static function languages()
        {
            $self  = self::getInstance();
            $langs = self::getLanguages();
            if(($parm = $self->router()->getRoutePart(-1)) !== false)
            {
                switch($parm)
                {
                    case 'select':
                        $langselect = array(''=>'[Please select]');
                        foreach(array_values($langs) as $l)
                            $langselect[$l] = $l;
                        $form = self::initForm();
                        $form->loadFile('forms.inc.php',__dir__.'/forms');
                        $form->setForm('lang_select');
                        $form->getElement('language')->setAttr('options',$langselect);
                        CAT_Helper_JSON::printSuccess($form->getForm());
                        break;
                }
            }
            echo CAT_Helper_JSON::printSuccess();
        }   // end function languages()

        /**
         * show the login page
         *
         * @access public
         * @return
         **/
        public static function login()
        {
            // we need this twice!
            $username_fieldname = CAT_Helper_Validate::createFieldname('username_');
            // for debugging
            $self = self::getInstance();
			$tpl_data = array(
                'USERNAME_FIELDNAME'    => $username_fieldname,
                'PASSWORD_FIELDNAME'    => CAT_Helper_Validate::createFieldname('password_'),
                'TOKEN_FIELDNAME'       => CAT_Helper_Validate::createFieldname('token_'),
                'USERNAME'              => CAT_Helper_Validate::sanitizePost($username_fieldname),
                'ENABLE_TFA'            => CAT_Registry::get('enable_tfa'),
            );
            $self->log()->addDebug('printing login page');
            $self->tpl()->output('login',$tpl_data);
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

        /**
         *  Print the admin header
         *
         *  @access public
         *  @return void
         */
        public static function print_header()
        {
            $tpl_data = array();
            $menu     = self::getMainMenu();

            // init template search paths
            self::initPaths();

            // the original list, ordered by parent -> children (if the
            // templates renders the HTML output)
            $lb = CAT_Object::lb();
            $lb->set('__id_key','id');
            $lb->set('__title_key','title');

            $tpl_data['MAIN_MENU'] = $lb->sort($menu,0);

            // recursive list
            $tpl_data['MAIN_MENU_RECURSIVE'] = $lb->buildRecursion($menu);

            // render list (ul)
            $lb->set(array(
                'top_ul_class'     => 'nav',
                'ul_class'         => 'nav',
                'current_li_class' => 'active'
            ));
            $tpl_data['MAIN_MENU_UL'] = $lb->buildList($menu);

            self::log()->addDebug('printing header');
            self::tpl()->output('header', $tpl_data);
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
            $theme                 = CAT_Registry::get('DEFAULT_THEME');
            if($theme)
            {
                $backend_theme_version
                    = $self->db()->query(
                          "SELECT `version` from `:prefix:addons` where `directory`=:theme",
                          array('theme'=>$theme)
                      )->fetchColumn();
            }
            $data['THEME_VERSION'] = $backend_theme_version;
            $data['THEME_NAME']    = ucfirst($theme);

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

        /**
         * check if TFA is enabled for current user
         *
         * @access public
         * @return
         **/
        public static function tfa()
        {
            $user = new CAT_User(CAT_Helper_Validate::sanitizePost('user'));
            echo CAT_Helper_JSON::printSuccess($user->tfa_enabled());
        }   // end function tfa()

    }   // end class CAT_Backend
}