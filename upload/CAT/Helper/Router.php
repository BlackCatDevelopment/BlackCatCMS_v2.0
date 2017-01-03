<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2016 Black Cat Development
   @link            http://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

if(!class_exists('CAT_Helper_Router',false))
{
    class CAT_Helper_Router extends CAT_Object
    {
        // log level
        public    static $loglevel   = \Monolog\Logger::EMERGENCY;
        //public    static $loglevel   = \Monolog\Logger::DEBUG;
        // instance
        private   static $instance   = NULL;
        // full route
        private          $route      = NULL;
        // query string
        private          $query      = NULL;
        // controller name
        private          $controller = NULL;
        // function name
        private          $func       = NULL;
        // params
        private          $params     = NULL;
        // full name
        private          $handler    = NULL;
        // match public
        public           $protected  = false;
        // needed route permission
        private          $perm       = NULL;

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }

        /**
         * create a new route handler
         **/
        public function __construct()
        {
            // get route
            list($this->route,$this->query) = self::initRoute();
            // split route
            $params      = explode('/',str_replace('\\','/',$this->route));
            // first part of the route is the controller name
            $controller  = array_shift($params);
            // second item (if exists) is the function name; if
            // no function name is available, use index()
            $function    = (count($params) ? array_shift($params) : 'index');
            $this->addDebug(sprintf('controller [%s] function [%s]',$controller,$function));
            // if there are any items left, save as params
            if(count($params)) $this->params = $params;
            // the given param may be an item id
            if(is_numeric($function))
            {
                $this->params[] = $function;
                $function = 'index';
            }
            // controller class name
            $this->controller = 'CAT_' . ucfirst($controller);
            // function name
            $this->func       = $function;
        }   // end function __construct()

        /**
         *
         * @access public
         * @return
         **/
        public function dispatch()
        {
            $controller = $this->controller;
            $function   = $this->func;
            $this->log()->addDebug(
                sprintf(
                    'dispatching route [%s], controller [%s], function [%s], protected [%s]',
                    $this->route, $controller, $function, $this->protected
                )
            );
            // check route permissions
            if(
                    $this->protected
                && !$this->user()->hasPerm($this->perm)
            ) {
                $this->log()->error(
                    'Routing error: User [{user}] tried to access [{func}] in controller [{controller}]',
                    array('user'=>$this->user()->get('username'),'func'=>$function,'controller'=>$controller)
                );
                CAT_Object::printFatalError('Access denied');
            }
            // check if controller exists
            if(!class_exists($controller) || !is_callable(array($controller,$function)))
            {
                $this->log()->error(
                    'Routing error: No such controller [{controller}] or function not callable [{func}]',
                    array('controller'=>$controller,'func'=>$function)
                );
                CAT_Object::printFatalError('Access denied');
            }

            // hand over to controller
            return $controller::$function($this->getParam());
            exit;
        }   // end function dispatch()
        

        /**
         * accessor to private controller name
         *
         * @access public
         * @return
         **/
        public function getController()
        {
            if($this->controller) return $this->controller;
            return false;
        }   // end function getController()

        /**
         * accessor to private controller name
         *
         * @access public
         * @return
         **/
        public function setController($name)
        {
            $this->controller = $name;
        }   // end function setController()

        /**
         * accessor to private function name
         *
         * @access public
         * @return
         **/
        public function getFunction()
        {
            if($this->func) return $this->func;
            return false;
        }   // end function getFunction()

        /**
         * accessor to private function name
         *
         * @access public
         * @return
         **/
        public function setFunction($name)
        {
            if(!$name || !strlen($name))
            {
                $this->log()->error(
                    'Router error: setFunction called with empty function name'
                );
                return;
            }
            if(is_numeric($name))
            {
                $this->log()->error(
                    'Router error: setFunction called with numeric function name'
                );
                return;
            }

            $this->func = $name;
        }   // end function setFunction()

        /**
         * accessor to route handler
         *
         * @access public
         * @return
         **/
        public function getHandler()
        {
            if(!$this->handler)
            try {
                $class = $this->controller;
                $this->handler = $class::getInstance();
            }
            catch( Exception $e )
            {
                echo $e->getMessage();
                return false;
            }
            return $this->handler;
        }   // end function getHandler()

        /**
         *
         * @access public
         * @return
         **/
        public function getParam($index=-1,$shift=false)
        {
            if(!is_array($this->params)) return NULL;
            if($index == -1) { // last param
                end($this->params);
                $index = key($this->params);
            }
            if(!isset($this->params[$index])) return NULL;
            $value = $this->params[$index];
            if($shift)
                array_splice($this->params,$index,1);
            return $value;
        }   // end function getParam()
        

        /**
         * accessor to private route params array
         *
         * @access public
         * @return
         **/
        public function getParams()
        {
            if($this->params && is_array($this->params)) return $this->params;
            return false;
        }   // end function getParams()

        /**
         * accessor to private route (example: 'backend/dashboard')
         *
         * @access public
         * @return string
         **/
        public function getRoute()
        {
            if($this->route) return $this->route;
            return false;
        }   // end function getRoute()

        /**
         *
         * @access public
         * @return
         **/
        public function getRoutePart($index)
        {
            if($this->route)
            {
                $parts = explode('/',$this->route);
                if(is_array($parts) && count($parts))
                {
                    if($index == -1) { // last param
                        end($parts);
                        $index = key($parts);
                    }
                    if(isset($parts[$index])) return $parts[$index];
                }
            }
            return false;
        }   // end function getRoutePart()

        /**
         *
         * @access public
         * @return
         **/
        public function getQuery()
        {
            if($this->query) return $this->query;
            return false;
        }   // end function getQuery()
        

        /**
         * retrieve the route
         *
         * @access public
         * @return
         **/
        public static function initRoute($remove_prefix=NULL)
        {
            $route = NULL;
            $query = NULL;

            foreach(array_values(array('REQUEST_URI','REDIRECT_SCRIPT_URL','SCRIPT_URL','ORIG_PATH_INFO','PATH_INFO')) as $key)
            {
                if(isset($_SERVER[$key]))
                {
                    $route = parse_url($_SERVER[$key],PHP_URL_PATH);
                    $query = parse_url($_SERVER[$key],PHP_URL_QUERY);
                    break;
                }
            }
            if(!$route) { $route = '/'; }

            // remove params
            if(stripos($route,'?'))
                list($route,$ignore) = explode('?',$route,2);

            $path_prefix = str_ireplace(
                CAT_Helper_Directory::sanitizePath($_SERVER['DOCUMENT_ROOT']),
                '',
                CAT_Helper_Directory::sanitizePath(CAT_PATH)
            );

            // remove leading /
            if(!strpos($route,'/',0))
                $route = substr($route,1,strlen($route));

            // if there's a prefix to remove (needed for backend paths)
            if($remove_prefix)
            {
                $route = str_replace($remove_prefix,'',$route);
                $route = substr($route,1,strlen($route));
            }

            return array($route,$query);
        }   // end function initRoute()

        /**
         *
         * @access public
         * @return mixed
         **/
        public function match($pattern)
        {
            // if the pattern has brackets, we return the first match
            // if not, we return boolean true
            if(preg_match($pattern,$this->getRoute(),$m))
            {
                if(count($m) && strlen($m[0]))
                    return $m[0];
                return true;
            }
            return false;
        }   // end function match()

        /**
         * checks if the route is protected or not
         *
         * @access public
         * @return boolean
         **/
        public function isProtected()
        {
            return $this->protected;
        }   // end function isProtected()

        /**
         *
         * @access public
         * @return
         **/
        public function protect($needed_perm)
        {
            $this->log()->addDebug(sprintf(
                'protecting route [%s] with needed perm [%s]',
                $this->getRoute(), $needed_perm
            ));
            $this->protected = true;
            $this->perm      = $needed_perm;
        }   // end function protect()
        

    }
}
