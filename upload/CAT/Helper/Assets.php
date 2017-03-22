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

if ( ! class_exists( 'CAT_Helper_Assets' ) )
{

    if ( ! class_exists( 'CAT_Object', false ) ) {
	    @include dirname(__FILE__).'/../Object.php';
	}

	class CAT_Helper_Assets extends CAT_Object
	{
        // set debug level
        protected static $loglevel  = \Monolog\Logger::EMERGENCY;
        //protected static $loglevel  = \Monolog\Logger::DEBUG;
        protected static $instance  = NULL;
        // map type to content-type
        protected static $mime_map  = array(
            'css'   => 'text/css',
            'js'    => 'text/javascript',
            'png'   => 'image/png',
        );

        public static function getInstance()
        {
            if (!self::$instance)
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         * @access public
         * @return
         **/
        public static function serve($type,$files)
        {
            if(!count($files)) return false;

            if($type=='images')
            {
                foreach($files as $file)
                {
                    if(file_exists(CAT_ENGINE_PATH.'/'.$file))
                    {
                        copy(CAT_ENGINE_PATH.'/'.$file,CAT_PATH.'/assets/'.pathinfo($file,PATHINFO_BASENAME));
                        #header('Content-Type: '.self::$mime_map[strtolower(pathinfo($file,PATHINFO_EXTENSION))]);
                        echo CAT_URL.'/assets/'.pathinfo($file,PATHINFO_BASENAME);
                    }
                }
            }

            // create asset factory and pass engine path as basedir
            $factory = new \Assetic\Factory\AssetFactory(CAT_ENGINE_PATH);
            $fm      = new \Assetic\FilterManager();
            $factory->setFilterManager($fm);
            $factory->setDefaultOutput('assets/*');

            $filters = array();
            if($type=='css')
            {
                foreach(array('CssImportFilter','CATCssRewriteFilter','MinifyCssCompressorFilter','CssCacheBustingFilter') as $filter)
                {
                    $filterclass = '\Assetic\Filter\\'.$filter;
                    $fm->set($filter,new $filterclass());
                    $filters[] = $filter;
                }
            }

            self::getInstance()->log()->addDebug(sprintf('type [%s], number of files [%d]', $type, count($files)).print_r($files,1));

            // add assets
            $assets  = $factory->createAsset(
                $files,
                $filters
            );

            // create the asset manager instance
            $am = new \Assetic\AssetManager();
            $am->set('assets', $assets);
            // create the writer to save the combined file
            $writer = new \Assetic\AssetWriter(CAT_PATH);
            $writer->writeManagerAssets($am);
            return CAT_URL.'/'.$assets->getTargetPath();
        }   // end function serve()
    }
}