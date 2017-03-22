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

if (!class_exists('CAT_Helper_JSON'))
{
    class CAT_Helper_JSON 
    {
        /**
         * encodes $data to json; please note that errors are ignored!
         *
         * @access public
         * @param  array   $data - the data to be encoded
         * @param  boolean $exit - wether to exit() (default) or not
         * @return void
         **/
        public static function printData($data,$exit=true)
        {
            if(!headers_sent())
                header('Content-type: application/json');
            echo json_encode($data,true);
            if($exit) exit();
        }   // end function printData()

        /**
         * calls self::printResult() to format an error message
         *
         * @access public
         * @param  string  $message
         * @param  boolean $exit
         * @return JSON
         **/
        public static function printError($message,$exit=true)
        {
            self::printResult(false,$message,$exit);
        }   // end function printError()

        /**
         * calls self::printResult() to format a success message
         *
         * @access public
         * @param  string  $message
         * @param  boolean $exit
         * @return JSON
         **/
        public static function printSuccess($message,$exit=true)
        {
            self::printResult(true,$message,$exit);
        }   // end function printSuccess()

        /**
         * creates an array with 'success' and 'message' keys and encodes it
         * to JSON using json_encode(); $message will be translated using the
         * lang() method
         *
         * the JSON result is echo'ed; if $exit is set to true, exit()
         * is called
         *
         * if no header was sent, sets 'application/json' as content-type
         *
         * @access public
         * @param  boolean $success - success (true) or error (false)
         * @param  string  $message - the message to be printed
         * @param  boolean $exit    - wether to exit() (default) or not
         * @return void
         **/
        public static function printResult($success,$message,$exit=true)
        {
            if(!headers_sent())
                header('Content-type: application/json');
            $field = (
                is_scalar($message)
                ? 'message'
                : 'data'
            );
            $content = (
                is_scalar($message)
                ? CAT_Object::lang()->translate($message)
                : $message
            );
            echo json_encode(array(
                'success' => $success,
                $field    => $content
            ));
            if($exit) exit();
        }   // end function printResult()
    }
}
