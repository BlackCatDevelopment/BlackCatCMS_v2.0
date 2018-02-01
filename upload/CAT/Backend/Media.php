<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       2018 Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

namespace CAT\Backend;
use \CAT\Base as Base;

if (!class_exists('\CAT\Backend\Media'))
{
    class Media extends Base
    {
        /**
         * log level
         **/
        protected static $loglevel   = \Monolog\Logger::EMERGENCY;
        /**
         * current instance (singleton)
         **/
        protected static $instance = NULL;

        /**
         * PHP File Upload error message codes:
         * http://php.net/manual/en/features.file-upload.errors.php
         **/
        protected $error_messages = array(
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload',
            'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
            'max_file_size' => 'File is too big',
            'min_file_size' => 'File is too small',
            'accept_file_types' => 'Filetype not allowed',
            'max_number_of_files' => 'Maximum number of files exceeded',
            'max_width' => 'Image exceeds maximum width',
            'min_width' => 'Image requires a minimum width',
            'max_height' => 'Image exceeds maximum height',
            'min_height' => 'Image requires a minimum height',
            'abort' => 'File upload aborted',
            'image_resize' => 'Failed to resize image'
        );

        /**
         *
         * @access public
         * @return
         **/
        public static function getInstance()
        {
            if(!is_object(self::$instance))
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function details()
        {
            if(!self::user()->hasPerm('media_list'))
                self::printFatalError('You are not allowed for the requested action!');
            $mediaID = self::getMediaID(); // prints fatal error on fail
            $details = \CAT\Helper\Media::getAttributes($mediaID);
            if(self::asJSON())
            {
                \CAT\Helper\JSON::printData($details);
            } else {
                #\CAT\Backend::print_header();
                self::tpl()->output('backend_media_details', $details);
                #\CAT\Backend::print_footer();
            }
        }   // end function details()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $subdir = self::getPath();
            $home   = self::user()->getHomeFolder();
            $dirs   = \CAT\Helper\Media::getFolders();

            // add some info for easier layout
            foreach($dirs as $index => $dir) {
                $dirs[$index]['level'] = count(explode('/',$dir['path']));
                $dirs[$index]['name']  = pathinfo($dir['path'],PATHINFO_FILENAME);
            }

            $tpl_data = array(
                'dirs'        => $dirs,
                'files'       => self::list($home.'/'.urldecode($subdir),true),
                'curr_folder' => urldecode(\CAT\Helper\Directory::sanitizePath($subdir)),
                'media_url'   => \CAT\Helper\Validate::sanitize_url(CAT_SITE_URL.'/'.\CAT\Registry::get('media_directory').'/'.urldecode(\CAT\Helper\Directory::sanitizePath($subdir))),
            );

            if(self::asJSON())
            {
                \CAT\Helper\Json::printData($tpl_data);
            }
            else
            {
                \CAT\Backend::print_header();
                self::tpl()->output('backend_media', $tpl_data);
                \CAT\Backend::print_footer();
            }
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function list($path,$return=false)
        {
            if(!$path)
            {
                $subfolder = self::getPath();
                $path      = self::user()->getHomeFolder(true) . '/' . $subfolder;
            } else {
                $subfolder = '';
            #    $path      = self::user()->getHomeFolder(true) . '/' . $path;
            }

            // check permissions
            if(!self::user()->hasPathPerm($path))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested list access to path [%s], missing path permission',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }

            // validate path
            if(!\CAT\Helper\Directory::checkPath($path,'media'))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested access to path [%s], invalid path (outside MEDIA)',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }

            $filter    = \CAT\Helper\Validate::sanitizePost('filter');

            // make sure we have all the data
            \CAT\Helper\Media::updateFiles($path,$filter);

            $files     = \CAT\Helper\Media::getFiles($path);
            $depth     = 0;

            $parts = (substr_count($subfolder,'/')>1 ? explode('/',$subfolder) : array());
            $depth = count($parts);

            $result = array(
                'files'  => $files,
                'dirs'   => \CAT\Helper\Media::getFiles($path),
                'folder' => \CAT\Helper\Directory::getName($subfolder),
                'depth'  => $depth,
            );

            if($return)
            {
                return $result['files'];
            }

            if(self::asJSON())
            {
                \CAT\Helper\Json::printData($result);
            }
            else
            {
                \CAT\Backend::print_header();
                self::tpl()->output('backend_media', $result);
                \CAT\Backend::print_footer();
            }
        }   // end function list()

        /**
         *
         * @access public
         * @return
         **/
        public static function protect()
        {
            if(!self::user()->hasPerm('media_folder_protect'))
                self::printFatalError('You are not allowed for the requested action!');

            $path = self::getPath();
            $path = urldecode($path);

            if(!self::user()->hasPathPerm($path))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested upload access to path [%s], missing path permission',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }

            // load templates
            $data = array(
                'password' => self::generatePassword(),
                'filename' => self::user()->getHomeFolder().$path.'/.htpasswd'
            );
            $htpasswd = self::tpl()->get(dirname(__FILE__).'/../templates/htpasswd.tpl',$data);
            $htaccess = self::tpl()->get(dirname(__FILE__).'/../templates/htaccess.tpl',$data);

            // save
            $fh = fopen(self::user()->getHomeFolder().\CAT\Helper\Directory::getName($path).'/.htpasswd','w');
            fwrite($fh,$htpasswd);
            fclose($fh);

            $fh = fopen(self::user()->getHomeFolder().$path.'/.htaccess','w');
            fwrite($fh,$htaccess);
            fclose($fh);

            // update db
            $sth = self::db()->query(
                'UPDATE `:prefix:media_dirs` SET `protected`=1 WHERE `path` like ?',
                array(ltrim($path,'/').'%')
            );

        }   // end function protect()

        /**
         *
         * @access public
         * @return
         **/
        public static function unprotect()
        {
            if(!self::user()->hasPerm('media_folder_protect'))
                self::printFatalError('You are not allowed for the requested action!');

            $path = self::getPath();
            $path = urldecode($path);

            if(!self::user()->hasPathPerm($path))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested upload access to path [%s], missing path permission',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }

            // remove
            unlink(self::user()->getHomeFolder().$path.'/.htpasswd');
            unlink(self::user()->getHomeFolder().$path.'/.htaccess');

            // update db
            $sth = self::db()->query(
                'UPDATE `:prefix:media_dirs` SET `protected`=null WHERE `path` like ?',
                array(ltrim($path,'/').'%')
            );

        }   // end function unprotect()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function upload()
        {
            if(!self::user()->hasPerm('media_upload'))
                self::printFatalError('You are not allowed for the requested action!');

            $base   = self::user()->getHomeFolder();
            $folder = \CAT\Helper\Validate::sanitizePost('folder');
            if($folder)
                $path = \CAT\Helper\Directory::sanitizePath($base.'/'.$folder);
            else
                $path = $folder;

            if(!self::user()->hasPathPerm($path))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested upload access to path [%s], missing path permission',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }

            list($ok,$errors) = \CAT\Helper\Upload::upload('files',$path);
/*
{"files": [
  {
    "name": "picture1.jpg",
    "size": 902604,
    "url": "http:\/\/example.org\/files\/picture1.jpg",
    "thumbnailUrl": "http:\/\/example.org\/files\/thumbnail\/picture1.jpg",
    "deleteUrl": "http:\/\/example.org\/files\/picture1.jpg",
    "deleteType": "DELETE"
  },
  {
    "name": "picture2.jpg",
    "size": 841946,
    "url": "http:\/\/example.org\/files\/picture2.jpg",
    "thumbnailUrl": "http:\/\/example.org\/files\/thumbnail\/picture2.jpg",
    "deleteUrl": "http:\/\/example.org\/files\/picture2.jpg",
    "deleteType": "DELETE"
  }
]}
*/
            if(self::asJSON()) {
                \CAT\Helper\Json::printData(array('success'=>$ok,'errors'=>$errors));
            }
        }   // end function upload()
        
        protected static function generatePassword($length = 12) {
            $r    = array_merge(range("a", "z"), range("a", "z"), range("A", "Z"), range(1, 9), range(1, 9));
            $not  = array('i', 'l', 'o', 'I', 'O');
            $r    = array_diff($r, $not);
            shuffle($r);
            $pass = array_slice($r, 0, intval($length));
            return implode("", $pass);
        } // generatePassword()

        /**
         * tries to retrieve 'media_id' by checking (in this order):
         *
         *    - $_POST['page_id']
         *    - $_GET['page_id']
         *    - Route param['page_id']
         *
         * also checks for numeric value
         *
         * @access private
         * @return integer
         **/
        protected static function getMediaID()
        {
            $mediaID  = \CAT\Helper\Validate::sanitizePost('media_id','numeric',NULL);

            if(!$mediaID)
                $mediaID  = \CAT\Helper\Validate::sanitizeGet('media_id','numeric',NULL);

            if(!$mediaID)
                $mediaID = self::router()->getParam(-1);

            if(!$mediaID || !is_numeric($mediaID))
                $mediaID = NULL;

            return $mediaID;
        }   // end function getPageID()

        /**
         *
         * @access private
         * @return
         **/
        private static function getPath()
        {
            //$base  = CAT_BACKEND_PATH.'/'.CAT_Backend::getArea();
            $base = CAT_BACKEND_PATH.'/'.\CAT\Registry::get('media_directory');

            // example route: backend/media/index/video
            //                backend/media/list/video
            // ...where 'video' is the name of the requested sub folder
            $route = self::router()->getRoute();
            $subdir = NULL;

            if($route != $base)
                $subdir = str_ireplace(array($base.'/index',$base.'/list',$base.'/protect',$base.'/unprotect',$base),'',$route);

            if($subdir=='/') $subdir = '';

            return $subdir;
        }   // end function getPath()
        

    } // class \CAT\Helper\Media

} // if class_exists()