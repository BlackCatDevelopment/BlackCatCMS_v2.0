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
 *   ---------------------------------------------------------------------------
 *   THIS FILE IS FOR BACKWARD COMPATIBILITY ONLY AND WILL BE REMOVED IN
 *   FUTURE VERSIONS!
 *   ---------------------------------------------------------------------------
 *
 */


if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'CAT_Users', false ) )
{
	class CAT_Users extends CAT_Object
	{
        // singleton
        private static $instance        = NULL;

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
    }
}

/*******************************************************************************
 * http://aaronsaray.com/blog/2009/02/12/password-complexity-class/
 ******************************************************************************/
class Password
{
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
}




?>