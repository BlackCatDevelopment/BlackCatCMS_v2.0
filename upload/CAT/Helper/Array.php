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

if ( ! class_exists( 'CAT_Helper_Array' ) )
{

    if ( ! class_exists( 'CAT_Object', false ) ) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	
	class CAT_Helper_Array extends CAT_Object
	{

        protected static $loglevel  = \Monolog\Logger::EMERGENCY;
        private   static $instance  = NULL;

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         * filter array
         *
         * Examples:
         *    Filter by key-value-pair
         *        $filtered = filter($array,array($key=>$value));
         *
         * @access  public
         * @param   array    input array
         * @param   mixed    filter options
         **/
        public static function filter()
        {
            $arguments = func_get_args();
            $array     = array_shift($arguments); // first arg must be the input array

            if(!is_array($array) || !count($array)) return array();

            $filterby = array_shift($arguments);
            $result   = new stdClass();

            // filter by key/value
            if(!is_callable($filterby) && is_scalar($filterby))
            {
                $key = $filterby;
                $val = array_shift($arguments);
                foreach($array as $k => $v)
                {
                    if(is_array($v))
                    {
                        if(array_key_exists($key,$v)) {
                            if($v[$key] !== $val) {
                                $result->{$k} = $v;
                            }
                        } else {
                            $result->{$k} = self::filter($v,$key,$val);
                        }
                    }
                    else
                    {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: not implemented yet
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        $result->{$k} = '?';
                    }
                }
            }
            return json_decode(json_encode($result), true);
        }   // end function filter()

        /**
         * sort an array
         *
         * @access public
         * @param  array   $array          - array to sort
         * @param  mixed   $index          - key to sort by
         * @param  string  $order          - 'asc' (default) || 'desc'
         * @param  boolean $natsort        - default: false
         * @param  boolean $case_sensitive - sort case sensitive; default: false
         *
         **/
        public static function sort($array,$index,$order='asc',$natsort=false,$case_sensitive=false)
        {
            if(is_array($array) && count($array))
            {
                 foreach(array_keys($array) as $key)
                     $temp[$key] = $array[$key][$index];
                 if(!$natsort)
                 {
                     ($order=='asc') ? asort($temp) : arsort($temp);
                 }
                 else
                 {
                     ($case_sensitive) ? natsort($temp) : natcasesort($temp);
                     if($order != 'asc')
                         $temp = array_reverse($temp,TRUE);
                 }
                 foreach(array_keys($temp) as $key)
                     (is_numeric($key)) ? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
                 return $sorted;
            }
            return $array;
        }   // end function sort()

        /**
         * search multidimensional array for $Needle
         *
         * @access public
         * @param  string  $Needle
         * @param  array   $Haystack
         * @param  string  $NeedleKey - optional
         * @param  boolean $Strict    - optional, default: false
         * @param  array   $Path      - needed for recursion
         * @return mixed   array (path) or false (not found)
         **/
        public static function ArraySearchRecursive( $Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array() )
        {

            if( ! is_array( $Haystack ) ) {
                return false;
            }
            reset($Haystack);
            foreach ( $Haystack as $Key => $Val ) {
                if (
                    is_array( $Val )
                    &&
                    $SubPath = self::ArraySearchRecursive($Needle,$Val,$NeedleKey,$Strict,$Path)
                ) {
                    $Path = array_merge($Path,Array($Key),$SubPath);
                    return $Path;
                }
                elseif (
                    ( ! $Strict && $Val  == $Needle && $Key == ( strlen($NeedleKey) > 0 ? $NeedleKey : $Key ) )
                    ||
                    (   $Strict && $Val === $Needle && $Key == ( strlen($NeedleKey) > 0 ? $NeedleKey : $Key ) )
                ) {
                    $Path[]=$Key;
                    return $Path;
                }
            }
            return false;
        }   // end function ArraySearchRecursive()

    }
}