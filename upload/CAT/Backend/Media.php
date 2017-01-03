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

if (!class_exists('CAT_Backend_Media'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Media extends CAT_Object
    {
        protected static $instance = NULL;

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
        public static function index()
        {
            $self  = self::getInstance();

            // TODO: hartverdrahtetes "backend" durch var ersetzen
            $base  = 'backend/'.CAT_Backend::getArea();

            // example route: backend/media/index/video
            // ...where 'video' is the name of the requested sub folder
            $route = $self->router()->getRoute();
            $subdir = NULL;
            if($route != $base)
                $subdir = str_ireplace(array($base.'/index/',$base),'',$route);

            $home = $self->user()->getHomeFolder();
            $dirs = CAT_Helper_Directory::getInstance()
                  ->setRecursion(true)
                  ->getDirectories($home,$home,true);

            $tpl_data = array(
                'dirs'  => $dirs,
                'files' => self::list($home.'/'.urldecode($subdir)),
                'curr_folder' => '/'.urldecode($subdir),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_media', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function index()

        /**
         *
         * @access public
         * @return
         **/
        public static function list($path)
        {
            $self   = self::getInstance();
            $filter = CAT_Helper_Validate::sanitizePost('filter');
            $paths  = array();
            $files  = array();

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: CHECK PERMISSIONS
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//$home = $self->user()->getHomeFolder();
            $files  = CAT_Helper_Media::getMediaFromDir($path,$filter);

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
        

    } // class CAT_Helper_Media

} // if class_exists()