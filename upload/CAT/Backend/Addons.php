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

if (!class_exists('CAT_Backend_Addons'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Backend_Addons extends CAT_Object
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
            $data = CAT_Helper_Addons::get_addons(0,NULL,NULL,NULL,false,true);
            foreach($data as $i => $item)
            {
                $data[$i]['install_date'] = CAT_Helper_DateTime::getDate($item['installed']);
                $data[$i]['update_date']  = CAT_Helper_DateTime::getDate($item['upgraded']);
            }
            $tpl_data = array(
                'modules'      => $data,
                'modules_json' => json_encode($data, JSON_NUMERIC_CHECK),
            );
            CAT_Backend::print_header();
            $self->tpl()->output('backend_addons', $tpl_data);
            CAT_Backend::print_footer();
        }   // end function Addons()

        /**
         *
         * @access public
         * @return
         **/
        public static function catalog()
        {
            if(!file_exists(CAT_ENGINE_PATH."/temp/catalog.json"))
            {
                self::update_catalog();
            }
            $catalog = self::get_catalog();
            // get installed
            $modules = CAT_Helper_Addons::get_addons();
            // map installed
            $installed = array();
            foreach($modules as $i => $m)
                $installed[$m['directory']] = $i;
            // find installed in catalog
            foreach( $catalog['modules'] as $i => $m)
            {
                if(isset($installed[$m['directory']]))
                {
                    $catalog['modules'][$i]['is_installed']   = true;
                }
                if(!isset($catalog['modules'][$i]['type']))
                {
                    $catalog['modules'][$i]['type'] = 'module';
                }
            }
            if(self::asJSON())
            {
                print json_encode(array('success'=>true,'modules'=>$catalog['modules']));
                exit();
            }
        }   // end function catalog()
        
        /**
         * get the catalog contents from catalog.json
         *
         * @access private
         * @return array
         **/
        private static function get_catalog()
        {
            $string    = file_get_contents(CAT_ENGINE_PATH."/temp/catalog.json");
            $catalog   = json_decode($string,true);
            if(is_array($catalog))
                return $catalog;
            else
                return array();
        }

        /**
         * update the catalog.json from GitHub
         *
         * @access private
         * @return void
         **/
        private static function update_catalog()
        {
            $ch   = CAT_Helper_GitHub::init_curl(GITHUB_CATALOG_LOCATION);
            $data = curl_exec($ch);
            if(curl_error($ch))
            {
                CAT_Object::json_error(trim(curl_error($ch)));
            }
            $fh = fopen(CAT_ENGINE_PATH.'/temp/catalog.json','w');
            if($fh)
            {
                fwrite($fh,$data);
                fclose($fh);
            }
            else
            {
                CAT_Object::json_error('Unable to save file');
            }
        }
        

    } // class CAT_Helper_Addons

} // if class_exists()