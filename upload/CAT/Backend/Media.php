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

if (!class_exists('CAT_Backend_Media'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Media extends CAT_Object
    {
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
            $details = CAT_Helper_Media::getAttributes($mediaID);
//print_r($details);
            if(self::asJSON())
            {
                CAT_Helper_JSON::printData($details);
            } else {
                #CAT_Backend::print_header();
                self::tpl()->output('backend_media_details', $details);
                #CAT_Backend::print_footer();
            }
        }   // end function details()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function index()
        {
            $base  = CAT_BACKEND_PATH.'/'.CAT_Backend::getArea();

            // example route: backend/media/index/video
            // ...where 'video' is the name of the requested sub folder
            $route = self::router()->getRoute();
            $subdir = NULL;

            if($route != $base)
                $subdir = str_ireplace(array($base.'/index',$base),'',$route);
            if($subdir=='/') $subdir = '';

            $home = self::user()->getHomeFolder();
            $dirs = CAT_Helper_Directory::getInstance()
                  ->setRecursion(true)
                  ->getDirectories($home,$home,true);

            $tpl_data = array(
                'dirs'  => $dirs,
                'files' => self::list($home.'/'.urldecode($subdir)),
                'curr_folder' => '/'.urldecode($subdir),
            );

            if(self::asJSON())
            {
                CAT_Helper_JSON::printData($tpl_data);
            }
            else
            {
                CAT_Backend::print_header();
                self::tpl()->output('backend_media', $tpl_data);
                CAT_Backend::print_footer();
            }
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function list($path)
        {
            if(!self::user()->hasPathPerm($path))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested list access to path [%s], missing path permission',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }
            if(!CAT_Helper_Directory::checkPath($path,'media'))
            {
                self::log()->addError(sprintf(
                    'User [%s] requested access to path [%s], invalid path (outside MEDIA)',
                    self::user()->get('display_name'), $path
                ));
                self::printFatalError('You are not allowed for the requested action!');
            }
            $filter = CAT_Helper_Validate::sanitizePost('filter');
            $paths  = array();
            $files  = CAT_Helper_Media::getMediaFromDir($path,$filter);

return $files;

            if(self::asJSON())
            {
                echo header('Content-Type: application/json');
                echo json_encode($files,true);
                return;
            }
            else
            {
                return $files;
            }
        }   // end function list()

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
            $folder = CAT_Helper_Validate::sanitizePost('folder');
            if($folder)
                $path = CAT_Helper_Directory::sanitizePath($base.'/'.$folder);
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

            list($ok,$errors) = CAT_Helper_Upload::upload('files',$path);
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
                CAT_Helper_JSON::printData(array('success'=>$ok,'errors'=>$errors));
            }
        }   // end function upload()
        

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
            $mediaID  = CAT_Helper_Validate::sanitizePost('media_id','numeric',NULL);

            if(!$mediaID)
                $mediaID  = CAT_Helper_Validate::sanitizeGet('media_id','numeric',NULL);

            if(!$mediaID)
                $mediaID = self::router()->getParam(-1);

            if(!$mediaID || !is_numeric($mediaID))
                $mediaID = NULL;

            return $mediaID;
        }   // end function getPageID()

    } // class CAT_Helper_Media

} // if class_exists()