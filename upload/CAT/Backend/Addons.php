<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

   @author          Black Cat Development
   @copyright       Black Cat Development
   @link            https://blackcat-cms.org
   @license         http://www.gnu.org/licenses/gpl.html
   @category        CAT_Core
   @package         CAT_Core

*/

namespace CAT\Backend;

use \CAT\Base as Base;
use \CAT\Backend as Backend;
use \CAT\Helper\Addons as HAddons;
use \CAT\Helper\DateTime as DateTime;
use \CAT\Helper\GitHub as GitHub;
use \CAT\Helper\Json as Json;

if (!class_exists('\CAT\Backend\Addons'))
{
    class Addons extends Base
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
            $data = HAddons::getAddons(NULL,'name',false,true); // all
            $ftp  = HAddons::getAddons(NULL,'name',false,true,true);
            foreach($data as $i => $item)
            {
                $data[$i]['install_date'] = DateTime::getDate($item['installed']);
                $data[$i]['update_date']  = DateTime::getDate($item['upgraded']);
            }
            $tpl_data = array(
                'modules'      => $data,
                'modules_json' => json_encode($data, JSON_NUMERIC_CHECK),
                'notinstalled' => $ftp,
                'notinstalled_json' => json_encode($ftp, JSON_NUMERIC_CHECK),
            );
            Backend::print_header();
            $self->tpl()->output('backend_addons', $tpl_data);
            Backend::print_footer();
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
            $modules = HAddons::getAddons(NULL,'name',false); // all
            // map installed
            $installed = array();
            foreach($modules as $i => $m)
                $installed[$m['directory']] = $m['version'];
            // find installed in catalog
            foreach($catalog['modules'] as $i => $m)
            {
                $catalog['modules'][$i]['upgradable']   = false;
                $catalog['modules'][$i]['is_installed'] = false;

                if(isset($installed[$m['directory']]))
                {
                    $catalog['modules'][$i]['is_installed'] = true;
                    $catalog['modules'][$i]['installed_version'] = $installed[$m['directory']];
                    if(version_compare($m['version'],$installed[$m['directory']],'>'))
                    {
                        $catalog['modules'][$i]['upgradable'] = true;
                    }
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
            $ch   = GitHub::init_curl(GITHUB_CATALOG_LOCATION);
            $data = curl_exec($ch);
            if(curl_error($ch))
            {
                Json::printError(trim(curl_error($ch)));
            }
            $fh = fopen(CAT_ENGINE_PATH.'/temp/catalog.json','w');
            if($fh)
            {
                fwrite($fh,$data);
                fclose($fh);
            }
            else
            {
                Json::printError('Unable to save file');
            }
        }
        

    } // class Addons

} // if class_exists()