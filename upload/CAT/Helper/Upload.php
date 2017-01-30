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
         * tries several methods to get the mime type of a file
         *
         * @access public
         * @return
         **/
        public function getMimeType()
        {
            // most secure method, uses file header
            // see http://getid3.sourceforge.net/ for a list of supported file types
            if(file_exists(CAT_PATH.'/modules/lib_getid3/getid3/getid3.php'))
            {
                self::log()->addDebug( '- Checking MIME type with getID3 library' );
                $mime = $this->getID3Mime();
            }
            // quite secure on *NIX systems
            elseif ($this->mime_file && substr(PHP_OS, 0, 3) != 'WIN')
            {
                self::log()->addDebug( 'Checking MIME type with UNIX file() command' );
                $mime = $this->getUNIXMime();
            }
            // still quite secure...
            elseif ($this->mime_fileinfo)
            {
                self::log()->addDebug( '- Checking MIME type with PECL extension' );
                $mime = $this->getPECLMime();
            }
            // NOT secure! Uses suffix only!
            elseif ($this->mime_magic)
            {
                self::log()->addDebug( '- Checking MIME type with mime.magic file (mime_content_type())' );
                $mime = $this->getMagicMime();
            }
            if($mime)
                $this->file_src_mime = $mime;
            else
                $this->file_src_mime = $this->mime_default_type;
        }   // end function getMimeType()

        /**
         * uses getID3 to get the mime type
         *
         * @access public
         * @return
         **/
        public function getID3Mime($filename)
        {
            $mime     = NULL;

        	if (!file_exists($filename))
            {
        		self::setError('File does not exist: "'.htmlentities($filename));
        		return false;
        	}
            elseif (!is_readable($filename))
            {
        		self::setError('File is not readable: "'.htmlentities($filename));
        		return false;
        	}

        	require_once CAT_PATH.'/modules/lib_getid3/getid3/getid3.php';

        	$getID3 = new getID3;
        	if ($fp = fopen($filename, 'rb'))
            {
        		$getID3->openfile($filename);
        		if (empty($getID3->info['error']))
                {
        			// ID3v2 is the only tag format that might be prepended in front of files, and it's non-trivial to skip, easier just to parse it and know where to skip to
        			getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'module.tag.id3v2.php', __FILE__, true);
        			$getid3_id3v2 = new getid3_id3v2($getID3);
        			$getid3_id3v2->Analyze();

        			fseek($fp, $getID3->info['avdataoffset'], SEEK_SET);
        			$formattest = fread($fp, 16);  // 16 bytes is sufficient for any format except ISO CD-image
        			fclose($fp);

        			$DeterminedFormatInfo = $getID3->GetFileFormat($formattest);
        			$mime = $DeterminedFormatInfo['mime_type'];
        		}
                else
                {
        			self::setError('Failed to getID3->openfile "'.htmlentities($filename));
        		}
        	}
            else
            {
        		self::setError('Failed to fopen "'.htmlentities($filename));
        	}
            self::log()->addDebug(sprintf(
                'MIME type detected as [%s] by getID3 library', $mime
            ));
        	return $mime;
        }   // end function getID3Mime()

        /**
         *
         * @access public
         * @return
         **/
        public function getMagicMime($filename)
        {
            $mime = NULL;

            if (function_exists('mime_content_type'))
            {
                $mime = mime_content_type($filename);
                self::log()->addDebug(sprintf(
                    'MIME type detected as [%s] by mime_content_type()', $mime
                ));
                if(preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                {
                    $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                    self::log()->addDebug(sprintf('MIME validated as [%s]', $mime));
                }
            }
            else
            {
                self::log()->addDebug('mime_content_type() is not available');
            }

            return $mime;
        }   // end function getMagicMime()

        /**
         *
         * @access public
         * @return
         **/
        public function getPECLMime($filename)
        {
            self::log()->addDebug('- Checking MIME type with Fileinfo PECL extension');
            $mime = NULL;

            if (function_exists('finfo_open'))
            {
                if ($this->mime_fileinfo !== '')
                {
                    if ($this->mime_fileinfo === true)
                    {
                        if (getenv('MAGIC') === FALSE)
                        {
                            if (substr(PHP_OS, 0, 3) == 'WIN')
                            {
                                $path = realpath(ini_get('extension_dir') . '/../') . 'extras/magic';
                            }
                            else
                            {
                                $path = '/usr/share/file/magic';
                            }
                            self::log()->addDebug( 'MAGIC path defaults to ' . $path );
                        }
                        else
                        {
                            $path = getenv('MAGIC');
                            self::log()->addDebug( 'MAGIC path is set to ' . $path . ' from MAGIC variable' );
                        }
                    }
                    else
                    {
                        $path = $this->mime_fileinfo;
                        self::log()->addDebug( 'MAGIC path is set to ' . $path );
                    }
                    $f = @finfo_open(FILEINFO_MIME, $path);
                }
                else
                {
                    self::log()->addDebug( 'MAGIC path will not be used' );
                    $f = @finfo_open(FILEINFO_MIME);
                }
                if (is_resource($f))
                {
                    $mime = finfo_file($f, realpath($this->file_src_pathname));
                    finfo_close($f);
                    self::log()->addDebug( 'MIME type detected as ' . $mime . ' by Fileinfo PECL extension' );
                    if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                    {
                        $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                        self::log()->addDebug( 'MIME validated as ' . $mime );
                    }
                }
                else
                {
                    self::log()->addDebug( 'Fileinfo PECL extension failed (finfo_open)' );
                }
            }   // end if (function_exists('finfo_open'))
            elseif (@class_exists('finfo'))
            {
                $f = new finfo( FILEINFO_MIME );
                if ($f)
                {
                    $mime = $f->file(realpath($this->file_src_pathname));
                    self::log()->addDebug( 'MIME type detected as ' . $mime . ' by Fileinfo PECL extension' );
                    if (preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                    {
                        $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                        self::log()->addDebug( 'MIME validated as ' . $mime );
                    }
                }
                else
                {
                    self::log()->addDebug( 'Fileinfo PECL extension failed (finfo)' );
                }
            }
            else
            {
                self::log()->addDebug( 'Fileinfo PECL extension not available' );
            }

            return $mime;
        }   // end function getPECLMime()

        /**
         *
         * @access public
         * @return
         **/
        public function getUNIXMime($filename)
        {
            $mime = NULL;

            // we've already checked this above, but the method may be called
            // from outside
            if (substr(PHP_OS, 0, 3) != 'WIN')
            {
                if (function_exists('exec'))
                {
                    if (strlen($mime = @exec("file -bi ".escapeshellarg($filename))) != 0)
                    {
                        $mime = trim($mime);
                        self::log()->addDebug(sprintf(
                            'MIME type detected as [%s] by UNIX file() command',$mime
                        ));
                        if(preg_match("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", $mime))
                        {
                            $mime = preg_replace("/^([\.-\w]+)\/([\.-\w]+)(.*)$/i", '$1/$2', $mime);
                            self::log()->addDebug(sprintf(
                                'MIME validated as [%s]', $mime
                            ));
                        }
                    }
                    else
                    {
                        self::log()->addDebug('UNIX file() command failed');
                    }
                }
                else
                {
                    self::log()->addDebug('PHP exec() function is disabled');
                }
            }
            else
            {
                self::log()->addDebug('UNIX file() command not available');
            }

            return $mime;
        }   // end function getUNIXMime()

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