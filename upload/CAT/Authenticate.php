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

if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'CAT_Authenticate', false ) )
{
	class CAT_Authenticate extends CAT_Object
	{
        // singleton
        private static $instance        = NULL;

		/*******************************************************************************
		 * http://aaronsaray.com/blog/2009/02/12/password-complexity-class/
		 ******************************************************************************/
		/** constants - are arbritrary numbers - but used for bitwise **/
		const REQUIRE_LOWERCASE       = 4;
		const REQUIRE_UPPERCASE       = 8;
		const REQUIRE_NUMBER	      = 16;
		const REQUIRE_SPECIALCHAR     = 32;
		//const REQUIRE_DIFFPASS	      = 64;
		const REQUIRE_DIFFUSER	      = 128;
		const REQUIRE_UNIQUE	      = 256;
		protected $_passwordDiffLevel = 3;
		protected $_uniqueChrRequired = 4;
		protected $_complexityLevel   = 0;
		protected $_issues            = array();

		protected $tfa;
		protected $tfaSecret			= NULL;

		protected $_hashOpt		= array(
				'algo'	=> 'PASSWORD_BCRYPT',
				'cost'	=> 10
		);


		/**
		 * inheritable constructor; allows to set object variables
		 **/
		public function __construct ( $options = array() ) {
			parent::__construct();
			$this->reset(); // make sure there is no old data

			$this->_hashOpt	= (object) $this->_hashOpt;
		}   // end function __construct()


		/**
		 * get singleton
		 **/
		public static function getInstance()
		{
			if (!self::$instance)
			{
				self::$instance = new self();
			}
			return self::$instance;
		}   // end function getInstance()


		/**
		* get hash
		 *
		 * @access private
		 * @param string $passwd
		 * @return string
		**/
		private function getHash($passwd)
		{
			return password_hash($passwd, $this->_hashOptions->algo, $this->_hashOptions->cost);
		}   // end function getHash()


		/**
		 * Compare user's password with given password
		 * @access public
		 * @param int $uid
		 * @param string $passwd
		 * @param string $tfaToken
		 * @return bool
		 */
		public static function authenticate($uid, $passwd, $tfaToken = NULL)
		{
			$this->log()->debug(sprintf('Trying to verify password for UserID [%s]',$userid));

			if(!$uid||!$passwd)
			{
				$this->setError('An empty value was sent for authentication!');
				return false;
			}

			$dbh  = CAT_Helper_DB::getInstance();

			$passwdHash		= $this->getHash($passwd);
			$storedHash		= $this->getPasswd($uid);
			$storedToken	= $this->getSecret($uid);

			if( password_verify( $storedHash, $passwdHash ) ) // user found and password ok
			{
				// 2-Step authentification
				// if TFA is not enabled (global or for the user) return true
				if( $storedToken === true ) return true;
				// if TFA is enabled and token is given
				else if( $storedToken && $tfaToken )
				{
					$tfa	= new \RobThree\Auth\TwoFactorAuth(WEBSITE_TITLE);
					if($tfa->verifyCode($storedToken,$tfaToken) !== true)
					{
						$this->reset();
						$this->setError('Two step authentication failed!');
						return false;
					}
				} else {
					// if TFA is enabled and token is not given
					$this->setError('TFA is enabled, but there is no token given!');
					return false;
				}
			}
			else
			{
				$this->setError('No such user, user not active, or invalid password!');
			}
			return false;
		}   // end function authenticate()


		/**
		 * Get hashed password from database
		 *
		 * @access private
		 * @param int $uid
		 * @return string
		 **/
		private function getPasswd($uid=NULL)
		{
			$dbh  = CAT_Helper_DB::getInstance();
			
			$storedHash	= $dbh->query(
				'SELECT `password` FROM `:prefix:rbac_users` WHERE `user_id`=:uid',
				array( 'uid' => $uid )
			)->fetchColumn();

			if(!$dbh->isError()) return false;
			else                 return $storedHash;

		}   // end function getPasswd()


		/**
		 * Check if the user is authenticated
		 *
		 * @access private
		 * @param int $uid
		 * @return string
		 **/
		private function getSecret($uid)
		{
			if(!ENABLE_TFA) return true;

			$dbh	= CAT_Helper_DB::getInstance();
			$getTFA	= $dbh->query(
				'SELECT `tfa_enabled`, `tfa_secret` FROM `:prefix:rbac_users` WHERE `username`=:name',
				array('name'=>$name )
			);

			if(!$dbh->isError() && $getTFA->rowCount() != 0) // user found and password ok
			{
				$tfa	= $getTFA->fetch(\PDO::FETCH_ASSOC);
				if( $tfa['tfa_enabled'] == 'Y') {
					return $tfa['tfa_secret'];
				}
				else return true;
			}
			else return false;

		}   // end function getSecret()


		/**
		 * create a new secret for a user
		 *
		 * @access public
		 * @return string
		 **/
		public function setSecret()
		{
			// generate a new secret
			if( is_null($this->tfaSecret) ) $this->tfaSecret	= $this->getTFAObject()->createSecret();
			return $this->tfaSecret;
		}   // end function createSecret()


		/**
		 * Check if the user is authenticated
		 *
		 * @access private
		 * @param int $uid
		 * @return string
		 **/
		private function getTFAObject()
		{
			if ( is_object($this->tfa) ) return $this->tfa;

			$ignore		= new CAT_Helper_QRCode(); // just to make sure the helper is loaded
			$mp			= new CAT_Helper_QRCodeProvider(); // needed for image creation

			$this->tfa	= new \RobThree\Auth\TwoFactorAuth(WEBSITE_TITLE, 6, 30, 'sha1', $mp);
			return $this->tfa;
		}   // end function getTFAObject()


		/**
		 * Check if the user is authenticated
		 *
		 * @access private
		 * @param int $uid
		 * @return string
		 **/
		public function createQRCode($uid)
		{
			return $this->getTFAObject()->getQRCodeImageAsDataUri(
					CAT_USER::getInstance($uid)->get('display_name'), $this->setSecret()
			);
		}   // end function getTFAObject()

        /**
         * Check if the user is authenticated
         *
         * @access public
         * @return boolean
         **/
        public function is_authenticated()
        {
        }   // end function is_authenticated()


		/**
		 * returns the standard options
		 * @return integer
		 */
		public function getComplexityStandard()
		{
			return self::REQUIRE_LOWERCASE + self::REQUIRE_UPPERCASE + self::REQUIRE_NUMBER;
		}
		/**
		 *returns all of the options
		 *@return integer
		 */
		public function getComplexityStrict()
		{
			$r = new ReflectionClass($this);
			$complexity = 0;
			foreach ($r->getConstants() as $constant) {
				$complexity += $constant;
			}
			return $complexity;
		}
		public function setComplexity($complexityLevel)
		{
			$this->_complexityLevel=$complexityLevel;
		}
		/**
		 * checks for complexity level. If returns false, it has populated the _issues array
		 */
		public function complexEnough($newPass, $username, $oldPass = NULL)
		{
			$enough = TRUE;
			$r      = new ReflectionClass($this);
			foreach ($r->getConstants() as $name=>$constant) {
				/** means we have to check that type then **/
				if ($this->_complexityLevel & $constant) {
					/** REQUIRE_MIN becomes _requireMin() **/
					$parts    = explode('_', $name, 2);
					$funcName = "_{$parts[0]}" . ucwords($parts[1]);
					$result   = call_user_func_array(array($this, $funcName), array($newPass, $oldPass, $username));
					if ($result !== TRUE) {
						$enough = FALSE;
						$this->_issues[] = $result;
					}
				}
			}
			return $enough;
		}
		public function getPasswordIssues()
		{
			return $this->_issues;
		}
		protected function _requireLowercase($newPass)
		{
			if (!preg_match('/[a-z]/', $newPass)) {
				return 'Password requires a lowercase letter.';
			}
			return true;
		}
		protected function _requireUppercase($newPass)
		{
			if (!preg_match('/[A-Z]/', $newPass)) {
				return 'Password requires an uppercase letter.';
			}
			return true;
		}
		protected function _requireNumber($newPass)
		{
			if (!preg_match('/[0-9]/', $newPass)) {
				return 'Password requires a number.';
			}
			return true;
		}
		protected function _requireSpecialChar($newPass)
		{
			if (!preg_match('/[^a-zA-Z0-9]/', $newPass)) {
				return 'Password requires a special character.';
			}
			return true;
		}
		protected function _requireDiffpass($newPass, $oldPass)
		{
			if (strlen($newPass) - similar_text($oldPass,$newPass) < $this->_passwordDiffLevel || stripos($newPass, $oldPass) !== FALSE) {
				return 'Password must be a bit more different than the last password.';
			}
			return true;
		}
		protected function _requireDiffuser($newPass, $oldPass, $username)
		{
			if (stripos($newPass, $username) !== FALSE) {
				return 'Password should not contain your username.';
			}
			return true;
		}
		protected function _requireUnique($newPass)
		{
			$uniques = array_unique(str_split($newPass));
			if (count($uniques) < $this->_uniqueChrRequired) {
				return 'Password must contain more unique characters.';
			}
			return true;
		}


        /**
         *
         * @access public
         * @return
         **/
        public function setError($msg)
        {
            $this->log()->debug($msg);
            $this->lasterror = $msg;
        }   // end function setError()
	}
}




?>