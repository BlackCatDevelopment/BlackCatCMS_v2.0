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

if (!class_exists('CAT_Helper_Captcha'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Captcha extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 7 );

        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * allow to use methods in OO context
         **/
        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         * get captcha; the difference between get() and show() is that get()
         * returns the Captcha HTML while show() prints it (echo)
         *
         **/
        public static function get($action='all',$style='',$sec_id='')
        {
            ob_start();
                if(!func_num_args()) self::show();
                else                 self::show($action,$style,$sec_id);
                $return = ob_get_clean();
            return $return;
        }

        /**
         * shows a captcha; uses securImage if it is installed and GD is
         * available; uses old WB style captchas if not
         *
         * @access public
         * @return mixed
         **/
        public static function show($action='all', $style='', $sec_id='')
        {
            // old style is used if:
            // + there are any function args (new method don't have some)
            // + no GD available
            // + no lib_securimage installed
            if(
                   func_num_args()
                || !CAT_Helper_Image::check_gd()
                || !file_exists(CAT_PATH.'/modules/lib_securimage/include/securimage.php')
            ) {
                return self::wbstyle($action, $style, $sec_id);
            }
            else
            {
                return self::securImage();
            }
        }   // end function show()

        /**
         *
         * @access public
         * @return
         **/
        public static function check() {
            $securimage = false;
            // SecurImage installed and GD available?
            if(file_exists(CAT_PATH.'/modules/lib_securimage/include/securimage.php') && CAT_Helper_Image::check_gd())
                $securimage = true;
            // WB style captcha was used anyway (why?!?)
            if(isset($_POST['captcha']) && !isset($_POST['catpcha_code']))
                $securimage = false;
            if(!$securimage)
                return self::wbstyle_check();
            else
                return self::securImage_check();
        }   // end function check()

        /**
         * shows a securImage Captcha; needs lib_securimage module
         *
         * @access public
         * @return
         **/
        private static function securImage() {
            echo '
            <div class="captcha_table"><div class="captcha_table_imgcalc">
                <span class="image_captcha">
					<img class="" id="image_captcha" src="'.CAT_URL.'/modules/lib_securimage/view.php" alt="Captcha" />
				</span>
				<input type="text" name="captcha_code" size="10" maxlength="6" />
                <a href="#" onclick="document.getElementById(\'image_captcha\').src = \''.CAT_URL.'/modules/lib_securimage/view.php?\' + Math.random(); return false">[ '
. CAT_Helper_I18n::getInstance()->translate('Different Image')
.' ]</a>
<object type="application/x-shockwave-flash" data="'.CAT_URL.'/modules/lib_securimage/include/securimage_play.swf?audio_file='.CAT_URL.'/modules/lib_securimage/include/securimage_play.php&amp;bgColor1=#fff&amp;bgColor2=#fff&amp;iconColor=#777&amp;borderWidth=1&amp;borderColor=#000" width="19" height="19">
  <param name="movie" value="'.CAT_URL.'/modules/lib_securimage/include/securimage_play.swf?audio_file='.CAT_URL.'/modules/lib_securimage/include/securimage_play.php&amp;bgColor1=#fff&amp;bgColor2=#fff&amp;iconColor=#777&amp;borderWidth=1&amp;borderColor=#000" />
</object>

            </div></div>';
        }   // end function securImage()

        /**
         *
         * @access private
         * @return
         **/
        private static function securImage_check() {
            self::getInstance()->log()->LogDebug('checking captcha',$_POST);
            include_once CAT_PATH.'/modules/lib_securimage/include/securimage.php';
            $securimage = new Securimage();
            $securimage->session_name = session_name();
            return $securimage->check($_POST['captcha_code']);
        }   // end function securImage_check()

        /**
         *
         * @access public
         * @return
         **/
        private static function wbstyle($action='all', $style='', $sec_id='') {
            @include CAT_PATH.'/CAT/Helper/Captcha/WB/captcha.php';
            return wb_call_captcha($action, $style, $sec_id);
        }   // end function wbstyle()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function wbstyle_check() {
            self::getInstance()->log()->LogDebug('checking captcha',$_POST);
            if(isset($_POST['captcha']) && $_POST['captcha'] != '')
            {
                $key = 'captcha';
                // Check for a mismatch
				if(!isset($_POST['captcha']) || !isset($_SESSION[$key]) || $_POST['captcha'] != $_SESSION[$key])
					return false;
                else
                    return true;
			}
            else
            {
                return false;
			}
        }   // end function wbstyle_check()
        

    } // class CAT_Helper_Captcha

} // if class_exists()