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

if (!class_exists('CAT_Helper_Directory'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Directory extends CAT_Object
    {
        protected static $loglevel     = \Monolog\Logger::EMERGENCY;
        protected static $is_win       = null;
        private   static $instance     = NULL;

        /**
         * get an instance of the directory class; optional param $reset
         * allows to reset all settings to default (example: $suffix_filter)
         *
         * @access public
         * @param  boolean  $reset
         * @return object
         **/
        public static function getInstance($reset=false)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            else
            {
                if($reset)
                    self::reset();
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * checks for valid path
         *
         * @access public
         * @param  string   $path
         * @param  string   $inside (default 'SITE')
         * @return boolean
         **/
        public static function checkPath($path,$inside='SITE')
        {
            $check = (strtoupper($inside) == 'SITE')
                   ? CAT_PATH
                   : ((strtoupper($inside) == 'MEDIA') ? self::user()->getHomeFolder() : CAT_ENGINE_PATH)
                   ;
            $check = self::sanitizePath($check);
            $path  = self::sanitizePath($path);
            if(substr_compare($path,$check,0,strlen($check),true)==0)
                return true;
            return false;
        }   // end function checkPath()

        /**
         * Create directories recursively
         *
         * @access public
         * @param  string   $dir_name - directory to create
         * @param  octal    $dir_mode - access mode
         * @return boolean
         **/
        public static function createDirectory($dir_name, $dir_mode=NULL, $createIndex=false)
        {
            if(!$dir_mode) {
                $dir_mode = CAT_Registry::exists('OCTAL_DIR_MODE')
                          ? CAT_Registry::get('OCTAL_DIR_MODE')
                          : (int) octdec(self::defaultDirMode());
            }
            $dir_name = self::sanitizePath($dir_name);
            if($dir_name != '' && !is_dir($dir_name))
            {
                $umask = umask(0);
                mkdir($dir_name, $dir_mode, true);
                umask($umask);
                if ( $createIndex )
                {
                    self::createIndex($dir_name);
                }
                return true;
            }
            return false;
        }   // end function createDirectory()

        /**
         * This method creates index.php files in every subdirectory of a given
         * path
         *
         * @access public
         * @param  string  $dir - directory to start with
         * @return boolean
         *
         **/
        public static function createIndex($dir)
        {
            if($handle=dir($dir))
            {
                if(!file_exists($dir.'/index.php'))
                {
                    $fh = fopen($dir.'/index.php', 'w');
                    fwrite($fh, '<' . '?' . 'php' . "\n");
        	        fclose($fh);
                }
                while(false !== ($file=$handle->read()))
                {
                    if($file != "." && $file != "..")
                    {
                        if(is_dir($dir.'/'.$file))
                        {
                            self::createIndex($dir.'/'.$file);
                        }
                    }
                }
                $handle->close();
                return true;
            }
            else {
                return false;
            }
        }   // end function createIndex()

		/**
		 * If the configuration setting 'string_dir_mode' is missing, we need
		 * a default value that fits most cases.
		 *
         * @access public
         * @return string
         **/
        public static function defaultDirMode() {
            return (!self::isWin())
                ? '0755'
                : '0777';
        }   // end function defaultDirMode()

        /**
         *
         * @access public
         * @return
         **/
        public static function defaultFileMode() {
            // we've already created some new files, so just check the perms they've got
            $check_for = dirname(__FILE__).'/../../../temp/logs/index.php';
            if(file_exists($check_for)) {
                $default_file_mode = octdec('0'.substr(sprintf('%o', fileperms($check_for)), -3));
            } else {
                $default_file_mode = '0777';
            }
            return $default_file_mode;
        }   // end function defaultFileMode()

        /**
         * converts extensions like .php to case insensitive glob pattern
         * like .[pP][hH][pP]
         *
         * @access public
         * @param  mixed  $ext - string or array of extensions
         * @return string
         **/
        public static function globPattern($ext)
        {
            $result = null;
            if(!is_array($ext)) { $ext = array($ext); }
            if(is_array($ext) && count($ext)) {
                $results = array();
                foreach(array_values($ext) as $item) {
                    $result = null;
                    foreach(str_split($item) as $char) {
                        if($char == '.') continue;
                        $result .= '['.strtolower($char).strtoupper($char).']';
                    }
                    $results[] = $result;
                }
                $result = implode(',',$results);
            }
            return $result;
        }   // end function globPattern()
        

        /**
         *
         * @access public
         * @param  string   $dir - path to start with
         * @param  array    $options - several options
         * @return array
         **/
        public static function findDirectories($dir,$options=array())
        {
            if(!is_dir($dir)) return array();

            // merge options with defaults
            $options = array_merge(array(
                'curr_depth'    => 0,       // pass current depth
                'max_depth'     => 9,       // max recursion depth
                'recurse'       => false,   // recurse or not
                'remove_prefix' => false,   // remove prefix or not
                'ignore'        => array(), // folders to ignore
            ), $options);

            $options['curr_depth']++;

            $dir = self::sanitizePath($dir);

            if(isset($options['remove_prefix']) && is_bool($options['remove_prefix']))
                $options['remove_prefix'] = self::sanitizePath($dir).'/';

            $directories = array();
            foreach(scandir($dir) as $file) {
                if(substr($file,0,1)=='.') continue;
                $curr_item = self::getName(self::sanitizePath($dir.'/'.$file));
                if(is_dir($curr_item)) {
                    $directories[] = str_ireplace($options['remove_prefix'],'',$curr_item);
                    if($options['recurse']===true && $options['curr_depth']<$options['max_depth']) {
                        $directories = array_merge($directories, self::findDirectories($curr_item,$options));
                    }
                }
            }

            return $directories;
        }   // end function findDirectories()

        /**
         *
         * @access public
         * @return
         **/
        public static function findFiles($dir,$options=array())
        {
            if(!is_dir($dir)) return array();

            // merge options with defaults
            $options = array_merge(array(
                'curr_depth'    => 0,     // pass current recursion depth
                'extension'     => null,  // file extension to scan for
                'extensions'    => null,  // array of extensions to scan for
                'filename'      => null,  // filename to scan for
                'max_depth'     => 9,     // max recursion depth
                'recurse'       => false, // recurse or not
                'remove_prefix' => false, // prefix to remove from path
                'remove_suffix' => false, // suffix to remove from path
            ), $options);

            $options['curr_depth']++;

            // add extension to scan for to extensions array
            if($options['extension'])
            {
                if(!is_array($options['extensions'])) {
                    $options['extensions'] = array($options['extension']);
                } else {
                    $options['extensions'][] = $options['extension'];
                }
                $options['extensions'] = array_unique($options['extensions']);
            }

            // sanitize extensions
            if(is_array($options['extensions']) && count($options['extensions']))
            {
                unset($options['extension']);
                for($i=0;$i<count($options['extensions']);$i++)
                {
                    $options['extensions'][$i] = preg_replace('~^\.~','',$options['extensions'][$i],1);
                }
            }

            $dir   = self::sanitizePath($dir);
            $files = array();

            if(
                   isset($options['remove_prefix'])
                && is_bool($options['remove_prefix'])
                && $options['remove_prefix'] === true
            ) {
                $options['remove_prefix'] = self::sanitizePath($dir);
            }

            foreach(scandir($dir) as $file)
            {
                if(substr($file,0,1)=='.') continue;
                $curr_item = self::getName(self::sanitizePath($dir.'/'.$file));
                if(is_file($curr_item))
                {
                    $filename = str_ireplace($options['remove_prefix'],'',$curr_item);
                    // filename match
                    if(
                           strlen($options['filename'])
                        && pathinfo($curr_item,PATHINFO_FILENAME) != $options['filename']
                    ) {
                        continue;
                    }
                    // extension match
                    if(
                           is_array($options['extensions'])
                        && count($options['extensions'])
                        && !in_array(pathinfo($curr_item,PATHINFO_EXTENSION),$options['extensions'])
                    ) {
                        continue;
                    }
                    $files[] = $filename;
                } else {
                    if(is_dir($curr_item) && $options['recurse']===true && $options['curr_depth']<$options['max_depth']) {
                        $files = array_merge($files, self::findFiles($curr_item,$options));
                    }
                }
                if(isset($options['filename']) && strlen($options['filename']) && count($files)) {
                    return $files[0];
                }
            }

            return $files;
        }   // end function findFiles()

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
            if(file_exists(CAT_ENGINE_PATH.'/modules/lib_getid3/getid3/getid3.php'))
            {
                self::log()->addDebug( '- Checking MIME type with getID3 library' );
                $mime = self::getID3Mime();
            }
            // quite secure on *NIX systems
            elseif ($this->mime_file && substr(PHP_OS, 0, 3) != 'WIN')
            {
                self::log()->addDebug( 'Checking MIME type with UNIX file() command' );
                $mime = self::getUNIXMime();
            }
            // still quite secure...
            elseif ($this->mime_fileinfo)
            {
                self::log()->addDebug( '- Checking MIME type with PECL extension' );
                $mime = self::getPECLMime();
            }
            // NOT secure! Uses suffix only!
            elseif ($this->mime_magic)
            {
                self::log()->addDebug( '- Checking MIME type with mime.magic file (mime_content_type())' );
                $mime = self::getMagicMime();
            }
            #if($mime)
            #    $this->file_src_mime = $mime;
            #else
            #    $this->file_src_mime = $this->mime_default_type;
            return $mime;
        }   // end function getMimeType()

        /**
         * get file modification date (timestamp)
         *
         * @access public
         * @param  string  $file
         * @return string
         **/
        public static function getModdate($file)
        {
            $file = self::sanitizePath($file);
            if(mb_detect_encoding($file,'UTF-8',true))
                $file = utf8_decode($file);
            if(is_dir($file)) return false;
            if(!file_exists($file)) return false;
    		$stat  = stat($file);
            $date  = isset($stat['mtime'])
                   ? $stat['mtime']
                   : NULL;
        	return $date;
        }   // end function getModdate()

        /**
         * returns the "real" filename (UTF-8)
         *
         * @access public
         * @param  string  $file
         * @return string
         **/
        public static function getName($file)
        {
            return (mb_detect_encoding($file,'UTF-8',true) ? $file : utf8_encode($file));
        }   // end function getName()

        /**
         * get file size
         *
         * @access public
         * @param  string  $file
         * @param  boolean $convert - call byte_convert(); default: false
         * @return string
         **/
        public static function getSize($file,$convert=false)
        {
            $file = self::sanitizePath($file);
            if(is_dir($file)) return false;
            if(!file_exists($file)) return false;
        	$size = @filesize($file);
        	if ($size < 0) {
        	    if (!self::isWin()) {
        		    $size = trim(`stat -c%s $file`);
        	    } else {
                    if(extension_loaded('COM')) {
                		$fsobj = new COM("Scripting.FileSystemObject");
                		$f = $fsobj->GetFile($file);
                		$size = $file->Size;
                	}
            	}
            }
            if($size && $convert) $size = self::humanize($size);
        	return $size;
        }   // end function getSize()

		/**
         * convert bytes to human readable string
         *
         * @access public
         * @param  integer $bytes
         * @return string
         **/
        public static function humanize($bytes)
        {
        	$symbol = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        	$exp = 0;
        	$converted_value = 0;
        	if ($bytes > 0)
        	{
        		$exp = floor( log($bytes) / log(1024));
        		$converted_value = ($bytes / pow( 1024, floor($exp)));
        	}
        	return sprintf('%.2f '.$symbol[$exp], $converted_value);
        }   // end function humanize()

        /**
         *
         * @access public
         * @return
         **/
        public static function isWin()
        {
            if(!self::$is_win)
            {
                self::$is_win = false;
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    self::$is_win = true;
                }
            }
            return self::$is_win;
        }   // end function isWin()

        /**
         * convert string to a valid filename
         *
         * @access public
         * @param  string  $string - filename
         * @return string
         **/
        public static function sanitizeFilename($string)
        {
            self::log()->addDebug('> sanitizeFilename [{file}]',array('file'=>$string));
            require_once(CAT_ENGINE_PATH . '/framework/functions-utf8.php');
            $string = entities_to_7bit($string);
            // remove all bad characters
            $bad    = array('\'', '"', '`', '!', '@', '#', '$', '%', '^', '&', '*', '=', '+', '|', '/', '\\', ';', ':', ',', '?','(',')');
            $string = str_replace($bad, '', $string);
            // replace multiple dots in filename to single dot and (multiple) dots at the end of the filename to nothing
            $string = preg_replace(array('/\.+/', '/\.+$/'), array('.', ''), $string);
            // replace spaces
            $string = trim($string);
            $string = preg_replace('/(\s)+/', '_', $string);
            // replace any weird language characters
            $string = str_replace(array('%2F', '%'), array('/', ''), urlencode($string));
            // remove path
            $string = pathinfo($string,PATHINFO_FILENAME);
            // Finally, return the cleaned string
            self::log()->addDebug('< sanitizeFilename result [{file}]',array('file'=>$string,__METHOD__,__LINE__));
            return $string;
        }   // end function sanitizeFilename()

        /**
         * fixes a path by removing //, /../ and other things
         *
         * @access public
         * @param  string  $path - path to fix
         * @return string
         **/
        public static function sanitizePath($path,$as_array=false)
        {
            self::log()->addDebug(sprintf('> sanitizePath(%s)',$path));

            // remove trailing slash; this will make sanitizePath fail otherwise!
            $path       = preg_replace( '~/{1,}$~', '', $path );
            // make all slashes forward
            $path       = str_replace( '\\', '/', $path );
            // bla/./bloo ==> bla/bloo
            $path       = preg_replace('~/\./~', '/', $path);

            // relative path
            if(strlen($path)>2 && !substr_compare($path,'..',0,2)) {
                if(defined('CAT_ENGINE_PATH')) {
                    $path = substr_replace($path, CAT_ENGINE_PATH, 1, 2);
                }
            }

            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts = array();
            foreach(explode('/',preg_replace('~/+~','/',$path)) as $part) {
                if($part === ".." || $part == '') {
                    array_pop($parts);
                }
                elseif($part!="")
                {
                    $part = (self::isWin() && mb_detect_encoding($part,'UTF-8',true))
                          ? utf8_decode($part)
                          : $part;
                    $parts[] = $part;
                }
            }

            if($as_array) return $parts;

            $new_path = implode("/", $parts);
            // windows
            if(!preg_match('/^[a-z]\:/i', $new_path)) {
                $new_path = '/' . $new_path;
            }
            self::log()->addDebug('< returning path [{path}]',array('path'=>$new_path),array(__METHOD__,__LINE__));
            return $new_path;
        }   // end function sanitizePath()

/*******************************************************************************
   PRIVATE METHODS
*******************************************************************************/

        /**
         * uses getID3 to get the mime type
         *
         * @access public
         * @param  string  $filename
         * @return string
         **/
        private static function getID3Mime($filename)
        {
            $mime = NULL;

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

        	require_once CAT_ENGINE_PATH.'/modules/lib_getid3/getid3/getid3.php';

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
        private static function getMagicMime($filename)
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
        private static function getPECLMime($filename)
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
        private static function getUNIXMime($filename)
        {
            $mime = NULL;
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
                self::log()->addDebug('UNIX file() command not available');
            }
            return $mime;
        }   // end function getUNIXMime()
    }
}