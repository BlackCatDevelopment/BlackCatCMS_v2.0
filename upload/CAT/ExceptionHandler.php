<?php

/**
 *
 *   @author          Black Cat Development
 *   @copyright       2013 - 2016, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_ExceptionHandler', false))
{
    class CAT_ExceptionHandler
    {
        public function __call($method, $args)
        {
            return call_user_func_array(array($this, $method), $args);
        }
        /**
         * exception handler; allows to remove paths from error messages and show
         * optional stack trace
         **/
        public static function exceptionHandler($exception)
        {
            $exc_class = get_class($exception);

            try {
                $logger = CAT_Object::log();
                $logger->emergency(sprintf(
                    'Exception with message [%s] emitted in [%s] line [%s]',
                    $exception->getMessage(),$exception->getFile(),$exception->getLine()
                ));
            } catch ( Exception $e ) {}

            // add stack trace
            if(isset($exc_class::$exc_trace) && $exc_class::$exc_trace === true)
            {
                $traceline = "#%s %s(%s): %s(%s)";
                $msg       = "Uncaught exception '%s' with message '%s'<br />"
                           . "<div style=\"font-size:smaller;width:80%%;margin:5px auto;text-align:left;\">"
                           . "in %s:%s<br />Stack trace:<br />%s<br />"
                           . "thrown in %s on line %s</div>"
                           ;
                $trace = $exception->getTrace();
                foreach ($trace as $key => $stackPoint) {
                    $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
                }
                // build tracelines
                $result = array();
                foreach ($trace as $key => $stackPoint) {
                    $result[] = sprintf(
                        $traceline,
                        $key,
                        ( isset($stackPoint['file']) ? $stackPoint['file'] : '-' ),
                        ( isset($stackPoint['line']) ? $stackPoint['line'] : '-' ),
                        $stackPoint['function'],
                        implode(', ', $stackPoint['args'])
                    );
                }
                // trace always ends with {main}
                $result[] = '#' . ++$key . ' {main}';
                // write tracelines into main template
                $msg = sprintf(
                    $msg,
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    implode("<br />", $result),
                    $exception->getFile(),
                    $exception->getLine()
                );
            }
            else
            {
                // filter message
                $message = $exception->getMessage();
                $message = str_replace(
                    array(
                        CAT_Helper_Directory::sanitizePath(CAT_PATH),
                        str_replace('/','\\',CAT_Helper_Directory::sanitizePath(CAT_PATH)),
                    ),
                    array(
                        '[path to]',
                        '[path to]',
                    ),
                    $message
                );
                $msg = "[$exc_class] $message";
            }
            // log
            $logger->emergency($msg);
            // show detailed error information to admin only
            $user = CAT_User::getInstance();
            if($user->is_authenticated() && $user->is_root())
                CAT_Object::printFatalError($msg);
            else
                CAT_Object::printFatalError("An internal error occured. We're sorry for inconvenience.");
        }

        /**
         * global error handler; allows to log the error
         **/
        public static function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context)
        {
            $error  = " | lvl : " . $error_level
                    . " | msg : " . $error_message
                    . " | file: " . $error_file
                    . " | ln  : " . $error_line;
            $logger = CAT_Object::log();
            switch ($error_level) {
                case E_DEPRECATED: // ignore
                case E_USER_DEPRECATED:
                    break;
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_PARSE:
                    $logger->alert($error);
                    break;
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                    $logger->error($error);
                    break;
                case E_WARNING:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_USER_WARNING:
                    $logger->warning($error);
                    break;
                case E_NOTICE:
                case E_USER_NOTICE:
                case E_STRICT:
                default:
                    $logger->info($error);
                    break;
            }
        }

        /**
         * global shutdown handler; allows to log errors that caused a shutdown
         **/
        public static function shutdownHandler() //will be called when php script ends.
        {
            $lasterror = error_get_last();
            switch ($lasterror['type'])
            {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                case E_PARSE:
                    $error = "[SHUTDOWN] "
                           . " | lvl : " . $lasterror['type']
                           . " | msg : " . $lasterror['message']
                           . " | file: " . $lasterror['file']
                           . " | ln  : " . $lasterror['line'];
                    
            }
        }
    }
}