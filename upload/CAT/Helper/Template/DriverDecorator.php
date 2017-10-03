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

if (!class_exists('CAT_Helper_Template_DriverDecorator'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Template_DriverDecorator extends CAT_Helper_Template
    {
        protected static $loglevel     = \Monolog\Logger::EMERGENCY;

        public    $template_block;
        protected $last         = NULL;
        private   $te           = NULL;
        private   $dirh         = NULL;
        private   $seen         = array();
        private   $paths        = array(
            'current'           => NULL,
            'frontend'          => NULL,
            'frontend_fallback' => NULL,
            'backend'           => NULL,
            'backend_fallback'  => NULL,
            'workdir'           => NULL
        );
        private   $search_order = array(
            'current', 'frontend', 'frontend_fallback', 'backend', 'backend_fallback', 'workdir'
        );

        public function __construct( $obj )
        {
            parent::__construct();
            $this->te = $obj;
            // get current working directory
            $callstack = debug_backtrace();
            $this->te->paths['workdir']
                = ( isset( $callstack[0] ) && isset( $callstack[0]['file'] ) )
                ? CAT_Helper_Directory::sanitizePath(realpath(dirname($callstack[0]['file'])))
                : CAT_Helper_Directory::sanitizePath(realpath(dirname(__FILE__)));

            if (file_exists( $this->te->paths['workdir'].'/templates' ))
            {
                $this->te->paths['workdir'] .= '/templates';
            }
            $this->te->paths['current'] = $this->te->paths['workdir'];
            $this->dirh = CAT_Helper_Directory::getInstance();
        }

        public function __call($method, $args)
        {
            if ( ! method_exists( $this->te, $method ) )
                $this->log()->logCrit('No such method: ['.$method.']');
            switch(count($args))
            {
                case 0:
                    return $this->te->$method();
                case 1:
                    return $this->te->$method($args[0]);
                case 2:
                    return $this->te->$method($args[0], $args[1]);
                case 3:
                    return $this->te->$method($args[0], $args[1], $args[2]);
                case 4:
                    return $this->te->$method($args[0], $args[1], $args[2], $args[3]);
                case 5:
                    return $this->te->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
                default:
                    return call_user_func_array(array($this->te, $method), $args);
            }
            //return call_user_func_array(array($this->te, $method), $args);
        }

        /**
         * reset the template search path
         *
         * @access public
         * @param  string  $context (frontend|backend)
         * @return
         **/
        public function resetPath($context='frontend')
        {
            if(CAT_Backend::isBackend()) $context = 'backend';
            $this->log()->logDebug(sprintf('resetting path to [%s], context [%s]',$this->last,$context));
            if(!$this->last) return;
            $this->te->paths[$context]  = $this->last;
            $this->te->paths['current'] = $this->last;
            if(isset($this->te->paths[$context.'_fallback']))
                    $this->te->paths[$context.'_fallback'] = $this->last;
        }   // end function resetPath()

        /**
         * set current template search path
         *
         * @access public
         * @param  string  $path
         * @param  string  $context - frontend (default) or backend
         * @return boolean
         *
         **/
         public function setPath($path,$context='frontend')
         {
            if(CAT_Backend::isBackend()) $context = 'backend';
            $path = CAT_Helper_Directory::sanitizePath($path);
            $this->last = NULL;
            $this->log()->logDebug(sprintf('context [%s] path [%s]', $context, $path ));
            if ( file_exists( $path ) )
            {
                if(isset($this->te->paths[$context]))
                    $this->last = $this->te->paths[$context];
                $this->te->paths[$context]  = $path;
                $this->te->paths['current'] = $path;
                if(!isset($this->te->paths[$context.'_fallback']))
                    $this->te->paths[$context.'_fallback'] = $path;
                return true;
            }
            else
            {
                $this->log()->logWarn( 'unable to set template path: does not exist!', $path );
                return false;
            }
        }   // end function setPath()

        /**
         * set template fallback path (for templates not found in default path)
         *
         * @access public
         * @param  string  $path
         * @param  string  $context - frontend (default) or backend
         * @return boolean
         *
         **/
        public function setFallbackPath($path,$context='frontend')
        {
            $path = CAT_Helper_Directory::sanitizePath($path);
            $this->log()->logDebug(sprintf('context [%s] fallback path [%s]', $context, $path ));
            if ( file_exists( $path ) ) {
                $this->te->paths[$context.'_fallback'] = $path;
            return true;
            }
            else
            {
                $this->log()->logWarn( 'unable to set fallback template path: does not exist!', $path );
                return false;
            }
        }   // end function setFallbackPath()

        /**
         * set global replacement values
         *
         * Usage
         *    $t->setGlobals( 'varname', 'value' );
         * or
         *    $t->setGlobals( array( 'var1' => 'val1', 'var2' => 'val2', ... ) );
         *
         * The second param is ignored if $var is an array
         *
         * @access public
         * @param  string || array  $var
         * @param  string           $value (optional)
         *
         **/
        public function setGlobals($var,$value=NULL)
        {
            $class = get_class($this->te);
            if(!is_array($var) && isset($value))
            {
               $class::$_globals[$var] = $value;
               return;
            }
            if(is_array($var))
            {
                foreach($var as $k => $v)
                {
                    if(!isset($class::$_globals[$k]))
                    {
                        $class::$_globals[$k] = $v;
                    }
                    else // allows to add items to already existing globals
                    {
                        if(is_array($class::$_globals[$k]))
                        {
                            if(is_array($v))
                            {
                                $class::$_globals[$k] = array_merge(
                                    $class::$_globals[$k],
                                    $v
                                );
                            }
                            else
                            {
                                $class::$_globals[$k][] = $v;
                            }
                        }
                    }
                }
            }

        }  // end function setGlobals()

        /**
         * check if template exists in current search path(s)
         **/
        public function hasTemplate($name)
        {
            $file = $this->findTemplate($name);
            if ( $file )
                return $file;
            else
                return false;
        }   // end function hasTemplate()

        /**
         *
         * @access public
         * @return
         **/
        public function findTemplate($_tpl)
        {
            // cached
            if(isset($this->seen[$this->te->paths['current'] . $_tpl]))
                return $this->seen[$this->te->paths['current'] . $_tpl];

            $suffix = pathinfo($_tpl,PATHINFO_EXTENSION);
            $has_suffix = ( $suffix != '' ) ? true : false;

            // scan search paths (if any)
            $paths = array();
            $s_paths = $this->te->paths;
            // sort paths by key; this sets 'workdir' to the end of the array
            ksort($s_paths);
            // move 'current' to begin
            $temp = array('current' => $s_paths['current']);
            unset($s_paths['current']);
            $s_paths = $temp + $s_paths;
            foreach($s_paths as $key => $value)
                if(isset($s_paths[$key]) && file_exists($s_paths[$key]))
                    $paths[] = $value;
            // remove doubles
            $paths = array_unique($paths);
            self::log()->logDebug('template search paths:',$paths);

            foreach($paths as $dir)
            {
                if($has_suffix && file_exists($dir.'/'.$_tpl))
                {
                    $file = $dir.'/'.$_tpl;
                }
                else
                {
                    $file = $this->dirh->findFiles($dir,array('filename'=>$_tpl,'extensions'=>array('tpl','htt')));
                }
                if ( $file )
                {
                    $this->seen[$this->te->paths['current'] . $_tpl] = $file;
                    return $file;
                }
            }
            self::log()->addEmergency( "The template [$_tpl] does not exist in one of the possible template paths!", $paths );
            // the template does not exists, so at least prompt an error
            $br = "\n";
            CAT_Object::printFatalError(
                "Unable to render the page (template: $_tpl)",
                NULL,true,
                $paths
            );
        }   // end function findTemplate()

    }
}