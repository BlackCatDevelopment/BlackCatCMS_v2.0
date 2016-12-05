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
            $self = self::getInstance();
            $home = $self->user()->getHomeFolder();
            $dirs = CAT_Helper_Directory::getInstance()
                  ->setRecursion(true)
                  ->getDirectories($home,$home,true);

            $tpl_data = array(
                'dirs'  => $dirs,
                'files' => self::list($home)
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_media', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function media()

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

            if(is_dir(CAT_PATH.'/media'))
                array_push($paths,CAT_PATH.'/media');
            if(is_dir(CAT_ENGINE_PATH.'/media'))
                array_push($paths,CAT_ENGINE_PATH.'/media');

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// TODO: Benutzer-Homeverzeichnis berücksichtigen
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            foreach($paths as $path)
            {
                $data = CAT_Helper_Media::getMediaFromDir($path,$filter);
                $files = array_merge($files,$data);
            }
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