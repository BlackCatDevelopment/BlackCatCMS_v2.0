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

if (!class_exists('CAT_Session', false))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/Object.php';
    }

    class CAT_Session extends CAT_Object implements SessionHandlerInterface
    {
        #protected static $loglevel = \Monolog\Logger::EMERGENCY;
        protected static $loglevel = \Monolog\Logger::DEBUG;

        private $w_stmt      = null;
        private $delete_stmt = null;
        private $gc_stmt     = null;
        private $key_stmt    = null;
        private $data_stmt   = null;

        private static $openssl_preferred = array(
            'aes-256-ctr',
            'aes-128-gcm',
        );
        private static $hash_algo_preferred = array(
            'sha512',
            'sha384',
            'sha256'
        );

        function __construct() {
            // set our custom session functions.
            session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'gc')
            );
            // This line prevents unexpected effects when using objects as save handlers.
            register_shutdown_function('session_write_close');
        }

        /**
         * start a new session
         *
         * @access public
         * @param  string  $session_name
         * @param  boolean $secure
         **/
        function start_session($session_name,$secure)
        {
            // Make sure the session cookie is not accessible via javascript.
            $httponly = true;

            // Hash algorithm to use for the session. (use hash_algos() to get a list of available hashes.)
            $session_hash = null;

            // Check if hash is available
            foreach(self::$hash_algo_preferred as $i => $session_hash)
            {
                if (in_array($session_hash, hash_algos())) {
                    ini_set('session.hash_function', $session_hash);
                    break;
                }
            }

            // How many bits per character of the hash.
            // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
            ini_set('session.hash_bits_per_character', 5);

            // Force the session to only use cookies, not URL variables.
            ini_set('session.use_only_cookies', 1);

            // Set the parameters
            session_set_cookie_params(
                time()+ini_get('session.gc_maxlifetime'),
                '/',
                '',
                $secure,
                $httponly
            );

            // Change the session name
            session_name($session_name);

            // Now we cat start the session
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

        	// Make sure the session hasn't expired, and destroy it if it has
        	if(self::validateSession())
        	{
        		// Check to see if the session is new or a hijacking attempt
        		if(!self::preventHijacking())
        		{
        			// Reset session data and regenerate id
        			$_SESSION = array();
        			$_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
        			$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        			self::regenerateSession();
        		} elseif(rand(1, 100) <= 5) {
        			self::regenerateSession();
        		}
        	} else {
        		$_SESSION = array();
        		session_destroy();
        		session_start();
        	}

            // This line regenerates the session and delete the old one.
            // It also generates a new encryption key in the database.
            #session_regenerate_id(true);

            return true;
        }

        /**
         * open session
         * @param  string  $save_path
         * @param  string  $session_name
         * @return boolean
         **/
        function open($save_path, $session_name) {
            self::log()->addDebug(sprintf(
                'opening new session; save_path [%s] session_name [%s]',
                $save_path, $session_name
            ));
            return true;
        }

        /**
         * close session
         * @return boolean
         **/
        function close() {
            self::log()->addDebug('closing session');
            return true;
        }

        /**
         * read session
         * @param  string  $id
         * @return mixed
         **/
        function read($id) {
            self::log()->addDebug(sprintf(
                'reading session [%s]', $id
            ));
            $data = $this->getData($id);
            $key = $this->getKey($id);
            $data = $this->decrypt($data, $key);
            self::log()->addDebug(var_export($data,1));
            return $data;
        }

        /**
         * save session data
         * @param  string  $id
         * @param  mixed  $data
         * @return boolean
         **/
        function write($id, $data) {
            self::log()->addDebug(sprintf(
                'storing session data, id [%s]', $id
            ));
            self::log()->addDebug('data: '.var_export($data,1));
            // Get unique key
            $key = $this->getKey($id);
            if(!$data || !strlen($data))
                $data = self::getData($id);
            else // Encrypt the data
                $data = $this->encrypt($data, $key);
            $time = time();
            if(!isset($this->w_stmt)) {
                $this->w_stmt = self::db()->prepare("REPLACE INTO `:prefix:sessions` (`id`, `set_time`, `data`, `session_key`) VALUES (?, ?, ?, ?)");
            }
            $this->w_stmt->execute(array($id, $time, $data, $key));
            return true;
        }

        /**
         * destroy session
         * @param  string  $id
         * @return boolean
         **/
        function destroy($id) {
            self::log()->addDebug(sprintf(
                'destroying session [%s]', $id
            ));
            if(!isset($this->delete_stmt)) {
                $this->delete_stmt = self::db()->prepare("DELETE FROM `:prefix:sessions` WHERE id = ?");
            }
            $this->delete_stmt->execute(array($id));
            return true;
        }

        /**
         * garbage collection
         * @param  string  $max
         * @return boolean
         **/
        function gc($max) {
            self::log()->addDebug('executing gc()');
            if(!isset($this->gc_stmt)) {
                $this->gc_stmt = self::db()->prepare("DELETE FROM `:prefix:sessions` WHERE `set_time` < ?");
            }
            $old = time() - $max;
            $this->gc_stmt->execute(array($old));
            return true;
        }

        /**
         * get session data
         * @param  string  $id
         * @return mixed
         **/
        private function getData($id) {
            if(!isset($this->data_stmt)) {
                $this->data_stmt = self::db()->prepare("SELECT `data` FROM `:prefix:sessions` WHERE `id` = ? LIMIT 1");
            }
            $this->data_stmt->execute(array($id));
            if($this->data_stmt->rowCount() == 1) {
                $data = $this->data_stmt->fetch();
                return $data['data'];
            }
            return '';
        }

        /**
         * get key
         * @param  string  $id
         * @return string
         **/
        private function getKey($id) {
            if(!isset($this->key_stmt)) {
                $this->key_stmt = self::db()->prepare("SELECT `session_key` FROM `:prefix:sessions` WHERE `id` = ? LIMIT 1");
            }
            $this->key_stmt->execute(array($id));
            if($this->key_stmt->rowCount() == 1) {
                $key = $this->key_stmt->fetch();
                return $key['session_key'];
            } else {
                $random_key = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
                return $random_key;
            }
        }

        /**
         * encrypt session data
         * @param  mixed   $data
         * @param  string  $key
         * @return string
         **/
        private function encrypt($data, $key)
        {
            if (extension_loaded('openssl')) {
                $cipher = self::getCipher();
                $ivlen  = openssl_cipher_iv_length($cipher);

                // Set a random salt
                $salt   = openssl_random_pseudo_bytes(16);
                $salted = '';
                $dx     = '';
                // Salt the key(32) and iv(16) = 48
                while (strlen($salted) < 32+$ivlen) {
                    $dx = hash('sha256', $dx.$key.$salt, true);
                    $salted .= $dx;
                }
                $key = substr($salted, 0, 32);
                $iv  = substr($salted, 32, $ivlen);
                $encrypted = openssl_encrypt($data, $cipher, $key, true, $iv);
                $encrypted = base64_encode($salt . $encrypted);
            } elseif (extension_loaded('mcrypt')) {
                $salt = 'cH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
                $key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
                $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
                $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
                $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv));
            } else {
                $encrypted = $data;
            }
            return $encrypted;
        }

        /**
         * decrypt session data
         * @param  mixed   $data
         * @param  string  $key
         * @return string
         **/
        private function decrypt($data, $key)
        {
            if (extension_loaded('openssl')) {
                $cipher = self::getCipher();
                $ivlen  = openssl_cipher_iv_length($cipher);
                $data   = base64_decode($data);
                $salt   = substr($data, 0, 16);
                $ct     = substr($data, 16);
                $rounds = 3; // depends on key length
                $data00 = $key.$salt;
                $hash = array();
                $hash[0] = hash('sha256', $data00, true);
                $result = $hash[0];
                for ($i = 1; $i < $rounds; $i++) {
                    $hash[$i] = hash('sha256', $hash[$i - 1].$data00, true);
                    $result .= $hash[$i];
                }
                $key = substr($result, 0, 32);
                $iv  = substr($result, 32, $ivlen);
                $decrypted = openssl_decrypt($ct, $cipher, $key, true, $iv);
            } elseif (extension_loaded('mcrypt')) {
                $salt = 'cH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
                $key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
                $iv = random_bytes(32);
                $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);
                $decrypted = rtrim($decrypted, "\0");
            } else {
                $decrypted = $data;
            }
            return $decrypted;
        }

        private static function getCipher()
        {
            $avail = openssl_get_cipher_methods();
            foreach(array_values(self::$openssl_preferred) as $method)
                if(in_array($method,$avail))
                    return $method;
        }

        private static function validateSession()
        {
        	if( isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES']) )
        		return false;
        	if(isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time())
        		return false;
        	return true;
        }

        private static function preventHijacking()
        {
        	if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent']))
        		return false;
        	if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR'])
        		return false;
        	if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
        		return false;
        	return true;
        }

        public static function regenerateSession()
        {
        	// If this session is obsolete it means there already is a new id
        	if(
                   isset($_SESSION['OBSOLETE'])
                && $_SESSION['OBSOLETE'] == true
            ) {
        		return;
            }

        	// Set current session to expire in 10 seconds
        	$_SESSION['OBSOLETE'] = true;
        	$_SESSION['EXPIRES'] = time() + 10;

        	// Create new session without destroying the old one
        	session_regenerate_id(false);

        	// Grab current session ID and close both sessions to allow other scripts to use them
        	$newSession = session_id();
        	session_write_close();

        	// Set session ID to the new one, and start it back up again
        	session_id($newSession);
        	session_start();

        	// Now we unset the obsolete and expiration values for the session we want to keep
        	unset($_SESSION['OBSOLETE']);
        	unset($_SESSION['EXPIRES']);
        }
    }
}