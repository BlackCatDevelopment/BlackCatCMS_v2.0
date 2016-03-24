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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

/**
 * Finally, a light, permissions-checking logging class.
 *
 * Originally written for use with wpSearch
 *
 * Usage:
 * $log = new KLogger('/var/log/', KLogger::INFO );
 * $log->logInfo('Returned a million search results'); //Prints to the log file
 * $log->logFatal('Oh dear.'); //Prints to the log file
 * $log->logDebug('x = 5'); //Prints nothing due to current severity threshhold
 *
 * @author  Kenny Katzgrau <katzgrau@gmail.com>
 * @since   July 26, 2008
 * @link    http://codefury.net
 * @version 0.1
 */

/**
 * Class documentation
 */
class CAT_Helper_KLogger
{
    /**
     * Error severity, from low to high. From BSD syslog RFC, secion 4.1.1
     * @link http://www.faqs.org/rfcs/rfc3164.html
     */
    const EMERG  = 0;  // Emergency: system is unusable
    const ALERT  = 1;  // Alert: action must be taken immediately
    const CRIT   = 2;  // Critical: critical conditions
    const ERR    = 3;  // Error: error conditions
    const WARN   = 4;  // Warning: warning conditions
    const NOTICE = 5;  // Notice: normal but significant condition
    const INFO   = 6;  // Informational: informational messages
    const DEBUG  = 7;  // Debug: debug messages

    //custom logging level
    /**
     * Log nothing at all
     */
    const OFF    = 8;
    /**
     * Alias for CRIT
     * @deprecated
     */
    const FATAL  = 2;

    /**
     * Internal status codes
     */
    const STATUS_LOG_OPEN    = 1;
    const STATUS_OPEN_FAILED = 2;
    const STATUS_LOG_CLOSED  = 3;

    /**
     * indentation
     **/
    private static $spaces = 0;
    /**
     * Current status of the log file
     * @var integer
     */
    private $_logStatus         = self::STATUS_LOG_CLOSED;
    /**
     * Holds messages generated by the class
     * @var array
     */
    private $_messageQueue      = array();
    /**
     * Path to the log file
     * @var string
     */
    private $_logFilePath       = null;
    /**
     * Current minimum logging threshold
     * @var integer
     */
    private $_severityThreshold = self::INFO;
    /**
     * This holds the file handle for this instance's log file
     * @var resource
     */
    private $_fileHandle        = null;

    /**
     * Standard messages produced by the class. Can be modified for il8n
     * @var array
     */
    private $_messages = array(
        //'writefail'   => 'The file exists, but could not be opened for writing. Check that appropriate permissions have been set.',
        'writefail'   => 'The file could not be written to. Check that appropriate permissions have been set.',
        'opensuccess' => 'The log file was opened successfully.',
        'openfail'    => 'The file could not be opened. Check permissions.',
        'stale'       => 'Stale file handle, trying to open it again',
    );
    
    /**
     * Default severity of log messages, if not specified
     * @var integer
     */
    private static $_defaultSeverity    = self::DEBUG;
    /**
     * Valid PHP date() format string for log timestamps
     * @var string
     */
    private static $_dateFormat         = 'Y-m-d G:i:s';
    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private static $_defaultPermissions = 0777;
    /**
     * Array of KLogger instances, part of Singleton pattern
     * @var array
     */
    private static $instances           = array();

    /**
     * Partially implements the Singleton pattern. Each $logDirectory gets one
     * instance.
     *
     * @param string  $logDirectory File path to the logging directory
     * @param integer $severity     One of the pre-defined severity constants
     * @return KLogger
     */
    public static function instance($logDirectory = false, $severity = false)
    {
        if ($severity === false) {
            $severity = self::$_defaultSeverity;
        }

        if ($logDirectory === false) {
            if (count(self::$instances) > 0) {
                return current(self::$instances);
            } else {
                $logDirectory = dirname(__FILE__);
            }
        }

        if (in_array($logDirectory, array_keys(self::$instances))) {
            return self::$instances[$logDirectory];
        }

        self::$instances[$logDirectory] = new self($logDirectory, $severity);

        return self::$instances[$logDirectory];
    }

    /**
     * Class constructor
     *
     * @param string  $logDirectory File path to the logging directory
     * @param integer $severity     One of the pre-defined severity constants
     * @return void
     */
    public function __construct($logDirectory, $severity)
    {
        $logDirectory = rtrim($logDirectory, '\\/');

        if ($severity === self::OFF) {
            return;
        }

        $this->_logFilePath = $logDirectory
            . DIRECTORY_SEPARATOR
            . (( $severity == self::DEBUG ) ? 'debug_' : 'log_')
            . date('Y-m-d')
            . '.txt';

        if ( $severity == self::DEBUG ) {
            $trace  = debug_backtrace();
            $caller = $trace[0];
            if(isset($caller['class']) && !preg_match('/klogger/i',$caller['class']))
                $this->_logFilePath = $logDirectory
                    . DIRECTORY_SEPARATOR
                    . 'debug_'
                    . $caller['class'].'_'
                    . date('Y-m-d')
                    . '.txt';
        }

        $this->_severityThreshold = $severity;
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, self::$_defaultPermissions, true);
        }

        if (file_exists($this->_logFilePath) && !is_writable($this->_logFilePath)) {
            $this->_logStatus = self::STATUS_OPEN_FAILED;
            $this->_messageQueue[] = $this->_messages['writefail'];
            return;
        }

        $filemode = ( $severity == self::DEBUG ) ? 'w' : 'a';
        #$filemode = 'a';

        if (false !== ($this->_fileHandle = fopen($this->_logFilePath, $filemode))) {
            $this->_logStatus = self::STATUS_LOG_OPEN;
            $this->_messageQueue[] = $this->_messages['opensuccess'] . ' ('.$filemode.')';
        } else {
            $this->_logStatus = self::STATUS_OPEN_FAILED;
            $this->_messageQueue[] = $this->_messages['openfail'];
        }
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->_fileHandle && is_resource($this->_fileHandle) && get_resource_type($this->_fileHandle)=='stream')
        {
            if(count($this->_messageQueue)>1)
            {
                fwrite($this->_fileHandle,"-----CLOSING HANDLE; KLOGGER MESSAGE QUEUE-----\n");
                fwrite($this->_fileHandle,var_export($this->_messageQueue,1));
            }
            $stat   = fstat($this->_fileHandle);
            $locked = false;
            // check if another process has locked the file
            if (flock($this->_fileHandle, LOCK_EX))
                flock($this->_fileHandle, LOCK_UN); // release the lock
            else
                $locked = true;
            $res = fclose($this->_fileHandle);
#            if(is_array($stat) && count($stat) && isset($stat['size']) && $stat['size'] == 0) // remove empty log files
#            {
#                if(!$locked && file_exists($this->_logFilePath) && is_writable($this->_logFilePath))
#                {
#                    @unlink($this->_logFilePath);        // remove the file
#                }
#            }
        }
    }
    /**
     * Writes a $line to the log with a severity level of DEBUG
     *
     * @param string $line Information to log
     * @return void
     */
    public function logDebug($line,$args=NULL)
    {
        $this->log($line, self::DEBUG, $args);
    }

    /**
     * Returns (and removes) the last message from the queue.
     * @return string
     */
    public function getMessage()
    {
        return array_pop($this->_messageQueue);
    }

    /**
     * Returns the entire message queue (leaving it intact)
     * @return array
     */
    public function getMessages()
    {
        return $this->_messageQueue;
    }

    /**
     * Empties the message queue
     * @return void
     */
    public function clearMessages()
    {
        $this->_messageQueue = array();
    }

    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param string $dateFormat Valid format string for date()
     */
    public static function setDateFormat($dateFormat)
    {
        self::$_dateFormat = $dateFormat;
    }

    /**
     * Writes a $line to the log with a severity level of INFO. Any information
     * can be used here, or it could be used with E_STRICT errors
     *
     * @param string $line Information to log
     * @return void
     */
    public function logInfo($line,$args=NULL)
    {
        $this->log($line, self::INFO, $args);
    }

    /**
     * Writes a $line to the log with a severity level of NOTICE. Generally
     * corresponds to E_STRICT, E_NOTICE, or E_USER_NOTICE errors
     *
     * @param string $line Information to log
     * @return void
     */
    public function logNotice($line,$args=NULL)
    {
        $this->log($line, self::NOTICE, $args);
    }

    /**
     * Writes a $line to the log with a severity level of WARN. Generally
     * corresponds to E_WARNING, E_USER_WARNING, E_CORE_WARNING, or
     * E_COMPILE_WARNING
     *
     * @param string $line Information to log
     * @return void
     */
    public function logWarn($line,$args=NULL)
    {
        $this->log($line, self::WARN, $args);
    }

    /**
     * Writes a $line to the log with a severity level of ERR. Most likely used
     * with E_RECOVERABLE_ERROR
     *
     * @param string $line Information to log
     * @return void
     */
    public function logError($line,$args=NULL)
    {
        $this->log($line, self::ERR, $args);
    }

    /**
     * Writes a $line to the log with a severity level of FATAL. Generally
     * corresponds to E_ERROR, E_USER_ERROR, E_CORE_ERROR, or E_COMPILE_ERROR
     *
     * @param string $line Information to log
     * @return void
     * @deprecated Use logCrit
     */
    public function logFatal($line,$args=NULL)
    {
        $this->log($line, self::FATAL, $args);
    }

    /**
     * Writes a $line to the log with a severity level of ALERT.
     *
     * @param string $line Information to log
     * @return void
     */
    public function logAlert($line,$args=NULL)
    {
        $this->log($line, self::ALERT, $args);
    }

    /**
     * Writes a $line to the log with a severity level of CRIT.
     *
     * @param string $line Information to log
     * @return void
     */
    public function logCrit($line,$args=NULL)
    {
        $this->log($line, self::CRIT, $args);
    }

    /**
     * Writes a $line to the log with a severity level of EMERG.
     *
     * @param string $line Information to log
     * @return void
     */
    public function logEmerg($line,$args=NULL)
    {
        $this->log($line, self::EMERG, $args);
    }

    /**
     * Writes a $line to the log with the given severity
     *
     * @param string  $line     Text to add to the log
     * @param integer $severity Severity level of log message (use constants)
     */
    public function log($line, $severity, $args=NULL)
    {
        if ($this->_severityThreshold >= $severity) {
            $status = $this->_getTimeLine($severity);
            $bt 	= debug_backtrace();
            $info   = array();
            while ( isset($bt[0]['class']) && $bt[0]['class'] == 'CAT_Helper_KLogger' ) {
                $last  = array_shift($bt);
			}
			if ( count($bt) ) {
				$info  = array_shift($bt);
			}
	        $class     = isset( $info['class'] )    ? $info['class']    : NULL;
	        $function  = isset( $info['function'] ) ? $info['function'] : NULL;
	        $file      = isset( $info['file'] )     ? basename($info['file']) : NULL;
	        $code_line = isset( $info['line'] )     ? $info['line']           : '?'
                       . isset( $last['line'] )     ? '('.$last['line'].')'   : '';

            if(substr($line,0,1)=='<')
                    self::$spaces--;
            self::$spaces = ( self::$spaces > 0 ? self::$spaces : 0 );
            $line = str_repeat('    ',self::$spaces).$line;
            if(substr($line,0,1)=='>')
                self::$spaces++;

	        $line      = "[$function()] $line [ $file:$code_line ]";
            $this->writeFreeFormLine("$status $line \n");
            if ( $args ) {
	            $dump = print_r( $args, 1 );
	            $dump = preg_replace( "/\r?\n/", "\n          ", $dump );
	            $this->writeFreeFormLine( print_r( $dump, 1 ) . "\n" );
	        }
        }
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param string $line Line to write to the log
     * @return void
     */
    public function writeFreeFormLine($line)
    {
        if ($this->_logStatus == self::STATUS_LOG_OPEN
            && $this->_severityThreshold != self::OFF) {
            if(get_resource_type($this->_fileHandle) !== 'stream') {
                $this->_messageQueue[] = $this->_messages['stale'];
                if (false !== ($this->_fileHandle = fopen($this->_logFilePath, 'a'))) {
            if (fwrite($this->_fileHandle, $line) === false) {
                $this->_messageQueue[] = $this->_messages['writefail'];
                    }
                }
            }
            else {
                if (fwrite($this->_fileHandle, $line) === false) {
                    $this->_messageQueue[] = $this->_messages['writefail'];
                }
            }
        }
    }

    private function _getTimeLine($level)
    {
        $time = date(self::$_dateFormat);

        switch ($level) {
            case self::EMERG:
                return "$time - EMERG -->";
            case self::ALERT:
                return "$time - ALERT -->";
            case self::CRIT:
                return "$time - CRIT -->";
            case self::FATAL: # FATAL is an alias of CRIT
                return "$time - FATAL -->";
            case self::NOTICE:
                return "$time - NOTICE -->";
            case self::INFO:
                return "$time - INFO -->";
            case self::WARN:
                return "$time - WARN -->";
            case self::DEBUG:
                return "$time - DEBUG -->";
            case self::ERR:
                return "$time - ERROR -->";
            default:
                return "$time - LOG -->";
        }
    }
}
