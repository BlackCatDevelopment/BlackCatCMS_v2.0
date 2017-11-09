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

if (!class_exists('CAT_Helper_Upload'))
{
    class CAT_Helper_Upload extends CAT_Object
    {
        protected static $loglevel = \Monolog\Logger::EMERGENCY;
        /**
         * last error
         **/
        private   static $error    = NULL;

        /**
         * Uploads a list of files, contained in $_FILES[$param_name]
         *
         * @access public
         * @param  string  $param_name - the fieldname of <input type="file" />
         *                               default: 'files'
         * @param  string  $folder     - destination folder
         * @param  boolean $overwrite  - allow overwrite or not (default)
         * @return
         **/
        public static function upload($param_name='files',$folder=NULL,$overwrite=false)
        {
            if(!$folder || $folder == '')
            {
                // use user's homedir
                $folder = self::user()->getHomeFolder();
            }

            // init
            $files  = array();
            $errors = array();
            $ok     = array();
            $multi  = ( isset($_FILES[$param_name]['name']) && is_array($_FILES[$param_name]['name']) )
                    ? true
                    : false
                    ;

            // multiple files?
            if($multi)
            {
                foreach ($_FILES[$param_name] as $k => $l)
                {
                    foreach ($l as $i => $v)
                    {
                        if (!array_key_exists($i, $files))
                            $files[$i] = array();
                        $files[$i][$k] = $v;
                    }
                }
            }
            else
            {
                $files[] = $_FILES[$param_name];
            }

            // do we have a list of upload handles?
            if(is_array($files) && count($files))
            {
                foreach($files as $file)
                {
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Image resizing
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
/*
  $handle->image_resize         = true;
  $handle->image_x              = 100;
  $handle->image_ratio_y        = true;
*/
                    $handle = new upload($file);
                    if ($handle->uploaded)
                    {
                        $handle->file_overwrite = $overwrite;
                        $handle->process($folder);
                        if ($handle->processed)
                            $ok[$handle->file_dst_name] = $handle->file_src_size;
                        else
                            $errors[$handle->file_src_name] = $handle->error;
                    }
                }
            }
            return array($ok,$errors);
        }   // end function upload()

        /**
         * get last error
         *
         * @access public
         * @return string
         **/
        public static function getError() {
            return self::$error;
        }   // end function getError()

        /**
         * save last error
         *
         * @access protected
         * @return void
         **/
        protected static function setError($msg)
        {
            self::log()->addError(sprintf('CAT_Helper_Upload error: [%s]',$msg));
            self::$error = $msg;
        }   // end function setError()

    }
}